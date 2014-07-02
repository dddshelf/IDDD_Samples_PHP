<?php

namespace SaasOvation\Collaboration\Port\Adapter\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use PDO;
use PDOStatement;
use BadMethodCallException;

use SaasOvation\Collaboration\Port\Adapter\Persistence\EventStoreProvider;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Event\Sourcing\DispatchableDomainEvent;
use SaasOvation\Common\Event\Sourcing\EventDispatcher;
use SaasOvation\Common\Event\Sourcing\EventNotifiable;


class FollowStoreEventDispatcher implements EventDispatcher, EventNotifiable
{
    /**
     * @var PDO
     */
    private $collaborationDataSource;

    /**
     * @var int
     */
    private $lastDispatchedEventId;

    /**
     * @var Collection
     */
    private $registeredDispatchers;

    public function __construct(PDO $aDataSource)
    {
        $this->setCollaborationDataSource($aDataSource);
        $this->setRegisteredDispatchers(new ArrayCollection());

        EventStoreProvider::instance()->eventStore()->registerEventNotifiable($this);

        $this->setLastDispatchedEventId($this->queryLastDispatchedEventId());

        $this->notifyDispatchableEvents();
    }

    public function dispatch(DispatchableDomainEvent $aDispatchableDomainEvent)
    {
        DomainEventPublisher::instance()->publish($aDispatchableDomainEvent->domainEvent());

        foreach ($this->registeredDispatchers() as $eventDispatcher) {
            $eventDispatcher->dispatch($aDispatchableDomainEvent);
        }
    }

    public function notifyDispatchableEvents()
    {
        // child EventDispatchers should use only
        // ConnectionProvider->connection() and
        // not commit. i will commit and close the
        // connection here

        $connection = $this->collaborationDataSource();

        try {
            $eventStore = EventStoreProvider::instance()->eventStore();
            $undispatchedEvents = $eventStore->eventsSince($this->lastDispatchedEventId());

            if (!$undispatchedEvents->isEmpty()) {

                foreach ($undispatchedEvents as $event) {
                    $this->dispatch($event);
                }

                $withLastEventId = $undispatchedEvents->get($undispatchedEvents->count() - 1);

                $lastDispatchedEventId = $withLastEventId->eventId();

                $this->setLastDispatchedEventId($lastDispatchedEventId);

                $this->saveLastDispatchedEventId($connection, $lastDispatchedEventId);
            }
        } catch (Exception $t) {
            throw new BadMethodCallException(
                sprintf('Cannot dispatch events because: %s', $t->getMessage()), $t->getCode(), $t
            );
        }
    }

    public function registerEventDispatcher(EventDispatcher $anEventDispatcher)
    {
        $this->registeredDispatchers()->add($anEventDispatcher);
    }

    public function understands(DispatchableDomainEvent $aDispatchableDomainEvent)
    {
        return true;
    }

    private function close(PDOStatement $aStatement)
    {
        $this->closeStatement($aStatement);
    }

    private function closeStatement(PDOStatement $aStatement)
    {
        if (null !== $aStatement) {
            try {
                $aStatement->closeCursor();
            } catch (Exception $e) {
                // ignore
            }
        }
    }

    private function collaborationDataSource()
    {
        return $this->collaborationDataSource;
    }

    private function setCollaborationDataSource(PDO $aDataSource)
    {
        $this->collaborationDataSource = $aDataSource;
    }

    private function connection()
    {
        return $this->collaborationDataSource();
    }

    private function lastDispatchedEventId()
    {
        return $this->lastDispatchedEventId;
    }

    private function setLastDispatchedEventId($aLastDispatchedEventId)
    {
        $this->lastDispatchedEventId = $aLastDispatchedEventId;
    }

    private function queryLastDispatchedEventId()
    {
        $lastHandledEventId = 0;
        $statement = null;
        $connection = $this->connection();

        try {
            $statement = $connection->prepare('select max(event_id) from tbl_dispatcher_last_event');
            $statement->execute();

            $lastHandledEventId = $statement->fetchColumn(1);

            if (false === $lastHandledEventId) {
                $this->saveLastDispatchedEventId($connection, 0);
                $lastHandledEventId = null;
            }
        } catch (Exception $e) {
            throw new BadMethodCallException(
                sprintf('Cannot query last dispatched event because: %s', $e->getMessage()), $e->getCode(), $e
            );
        } finally {
            $this->close($statement);
        }

        return $lastHandledEventId;
    }

    private function saveLastDispatchedEventId(
        PDO $aConnection,
        $aLastDispatchedEventId
    ) {

        $updated = 0;
        $statement = null;

        try {
            $statement = $aConnection->prepare('update tbl_dispatcher_last_event set event_id=?');
            $statement->bindParam(1, $aLastDispatchedEventId);
            $statement->execute();
            $updated = $statement->rowCount();

        } catch (Exception $e) {
            throw new BadMethodCallException('Cannot update dispatcher last event.');
        } finally {
            $this->closeStatement($statement);
        }

        if ($updated == 0) {

            try {
                $statement = $aConnection->prepare('insert into tbl_dispatcher_last_event values(?)');
                $statement->bindParam(1, $aLastDispatchedEventId);
                $statement->execute();

            } catch (Exception $e) {
                throw new BadMethodCallException('Cannot insert dispatcher last event.');
            } finally {
                $this->closeStatement($statement);
            }
        }
    }

    private function registeredDispatchers()
    {
        return $this->registeredDispatchers;
    }

    private function setRegisteredDispatchers($aDispatchers)
    {
        $this->registeredDispatchers = $aDispatchers;
    }
}
