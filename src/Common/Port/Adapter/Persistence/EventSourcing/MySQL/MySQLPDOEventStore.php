<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\MySQL;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PDOStatement;
use Exception;
use PDO;

use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Event\EventSerializer;
use SaasOvation\Common\Event\Sourcing\DispatchableDomainEvent;
use SaasOvation\Common\Event\Sourcing\EventNotifiable;
use SaasOvation\Common\Event\Sourcing\EventStore;
use SaasOvation\Common\Event\Sourcing\EventStoreAppendException;
use SaasOvation\Common\Event\Sourcing\EventStoreException;
use SaasOvation\Common\Event\Sourcing\EventStreamId;
use SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\DefaultEventStream;

class MySQLPDOEventStore implements EventStore, ContainerAwareInterface
{
    /**
     * @var MySQLPDOEventStore
     */
    private static $instance;

    /**
     * @var PDO
     */
    private $collaborationDataSource;

    /**
     * @var EventNotifiable
     */
    private $eventNotifiable;

    /**
     * @var EventSerializer
     */
    private $serializer;

    public static function instance()
    {
        return static::$instance;
    }

    public function __construct(PDO $aDataSource)
    {
        $this->setCollaborationDataSource($aDataSource);
        $this->setSerializer(EventSerializer::instance());
    }

    public function appendWith(EventStreamId $aStartingIdentity, Collection $anEvents)
    {
        // tbl_es_event_store must have a composite primary key
        // consisting of {stream_name}:{streamVersion} so that
        // appending a stale version will fail the pk constraint

        $connection = $this->connection();

        try {
            $index = 0;

            foreach ($anEvents as $event) {
                $this->appendEventStore($connection, $aStartingIdentity, $index++, $event);
            }

            $connection->commit();

            $this->notifyDispatchableEvents();

        } catch (Exception $t1) {
            try {
                $this->connection()->rollback();
            } catch (Exception $t2) {
                // ignore
            }

            throw new EventStoreAppendException(
                sprintf('Could not append to event store because: %s', $t1->getMessage()),
                $t1
            );
        }
    }

    public function close()
    {
        // no-op
    }

    public function eventsSince($aLastReceivedEvent)
    {
        $connection = $this->connection();

        $statement = null;

        try {
            $statement = $connection->prepare(
                'SELECT event_id, event_body, event_type
                 FROM tbl_es_event_store
                 WHERE event_id > ?
                 ORDER BY event_id'
            );

            $statement->bindParam(1, $aLastReceivedEvent);
            $statement->execute();

            $sequence = $this->buildEventSequence($statement);

            $connection->commit();

            return $sequence;

        } catch (Exception $t) {
            throw new EventStoreException(
                sprintf('Cannot query event for sequence since: %s because: %s', $aLastReceivedEvent, $t->getMessage()),
                $t
            );
        } finally {
            if (null !== $statement) {
                $statement->closeCursor();
            }
        }
    }

    public function eventStreamSince(EventStreamId $anIdentity)
    {
        $connection = $this->connection();

        $statement = null;

        try {
            $statement = $connection->prepare(
                'SELECT stream_version, event_type, event_body
                 FROM tbl_es_event_store
                 WHERE stream_name = ?
                   AND stream_version >= ?
                 ORDER BY stream_version'
            );

            $statement->bindValue(1, $anIdentity->streamName());
            $statement->bindValue(2, $anIdentity->streamVersion());
            $statement->execute();

            $eventStream = $this->buildEventStream($statement);

            if ($eventStream->version() == 0) {
                throw new EventStoreException(
                    sprintf(
                        'There is no such event stream: %s : %s',
                        $anIdentity->streamName(),
                        $anIdentity->streamVersion()
                    )
                );
            }

            $connection->commit();

            return $eventStream;

        } catch (Exception $t) {
            throw new EventStoreException(
                'Cannot query event stream for: '
                . $anIdentity->streamName()
                . ' since version: '
                . $anIdentity->streamVersion()
                . ' because: '
                . $t->getMessage(),
                $t
            );
        } finally {
            if (null !== $statement) {
                $statement->closeCursor();
            }
        }
    }

    public function fullEventStreamFor(EventStreamId $anIdentity)
    {
        $connection = $this->connection();

        $statement = null;

        try {
            $statement = $connection->prepare(
                'SELECT stream_version, event_type, event_body
                 FROM tbl_es_event_store
                 WHERE stream_name = ?
                 ORDER BY stream_version'
            );

            $statement->bindValue(1, $anIdentity->streamName());
            $statement->execute();

            $connection->commit();

            return $this->buildEventStream($statement);

        } catch (Exception $t) {
            throw new EventStoreException(
                sprintf(
                    'Cannot query full event stream for: %s because: %s',
                    $anIdentity->streamName(),
                    $t->getMessage()
                ),
                $t
            );
        } finally {
            if (null !== $statement) {
                $statement->closeCursor();
            }
        }
    }

    public function purge()
    {
        $connection = $this->connection();

        try {
            $connection->exec('DELETE FROM TBL_ES_EVENT_STORE');

            $connection->commit();

        } catch (Exception $t) {
            throw new EventStoreException(
                sprintf('Problem purging event store because: %s', $t->getMessage()),
                $t
            );
        }
    }

    public function registerEventNotifiable(EventNotifiable $anEventNotifiable)
    {
        $this->eventNotifiable = $anEventNotifiable;
    }

    private function appendEventStore(
        PDO $aConnection,
        EventStreamId $anIdentity,
        $anIndex,
        DomainEvent $aDomainEvent
    ) {

        $statement = $aConnection->prepare(
            'INSERT INTO tbl_es_event_store (event_body, event_type, stream_name, stream_version) VALUES (?, ?, ?, ?)'
        );

        $statement->bindValue(2, $this->serializer()->serialize($aDomainEvent));
        $statement->bindValue(3, get_class($aDomainEvent));
        $statement->bindValue(4, $anIdentity->streamName());
        $statement->bindValue(5, $anIdentity->streamVersion() + $anIndex, PDO::PARAM_INT);

        $statement->execute();
    }

    private function buildEventSequence(PDOStatement $anStatement)
    {
        $events = new ArrayCollection();

        while ($row = $anStatement->fetch(PDO::FETCH_ASSOC)) {
            $eventId = (int) $row['EVENT_ID'];
            $eventClassName = $row['EVENT_TYPE'];
            $eventBody = $row['EVENT_BODY'];

            $domainEvent = $this->serializer()->deserialize($eventBody, $eventClassName);

            $events->add(new DispatchableDomainEvent($eventId, $domainEvent));
        }

        return $events;
    }

    private function buildEventStream(PDOStatement $anStatement)
    {
        $events = new ArrayCollection();

        $version = 0;

        while ($row = $anStatement->fetch(PDO::FETCH_ASSOC)) {
            $version = (int) $row['STREAM_VERSION'];
            $eventClassName = $row['EVENT_TYPE'];
            $eventBody = $row['EVENT_BODY'];

            $domainEvent = $this->serializer()->deserialize($eventBody, $eventClassName);

            $events->add($domainEvent);
        }

        return new DefaultEventStream($events, $version);
    }

    private function setCollaborationDataSource(PDO $aDataSource)
    {
        $this->collaborationDataSource = $aDataSource;
    }

    private function connection()
    {
        return $this->collaborationDataSource;
    }

    private function eventNotifiable()
    {
        return $this->eventNotifiable;
    }

    private function notifyDispatchableEvents()
    {
        $eventNotifiable = $this->eventNotifiable();

        if (null !== $eventNotifiable) {
            $this->eventNotifiable()->notifyDispatchableEvents();
        }
    }

    private function serializer()
    {
        return $this->serializer;
    }

    private function setSerializer(EventSerializer $aSerializer)
    {
        $this->serializer = $aSerializer;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        static::$instance = $container->get('mysqlPdoEventStore');
    }
}
