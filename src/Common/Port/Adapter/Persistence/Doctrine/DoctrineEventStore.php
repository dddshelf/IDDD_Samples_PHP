<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Event\EventSerializer;
use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Event\StoredEvent;

class DoctrineEventStore extends AbstractDoctrineEntityManager implements EventStore
{
    public function allStoredEventsBetween($aLowStoredEventId, $aHighStoredEventId)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT s
             FROM SaasOvation\Common\Event\StoredEvent s
             WHERE s.eventId BETWEEN ?1 AND ?2
             ORDER BY s.eventId'
        );

        $query->setParameter(1, $aLowStoredEventId);
        $query->setParameter(2, $aHighStoredEventId);

        $storedEvents = $query->execute();

        return $storedEvents;
    }

    public function allStoredEventsSince($aStoredEventId)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT s
             FROM SaasOvation\Common\Event\StoredEvent s
             WHERE s.eventId > ?1
             ORDER BY s.eventId'
        );

        $query->setParameter(1, $aStoredEventId);

        $storedEvents = $query->execute();

        return new ArrayCollection($storedEvents);
    }

    public function append(DomainEvent $aDomainEvent)
    {
        $eventSerialization = EventSerializer::instance()->serialize($aDomainEvent);

        $storedEvent = new StoredEvent(
            get_class($aDomainEvent),
            $aDomainEvent->occurredOn(),
            $eventSerialization
        );

        $this->entityManager()->persist($storedEvent);
        $this->entityManager()->flush($storedEvent);

        return $storedEvent;
    }

    public function close()
    {
        // no-op
    }

    public function countStoredEvents()
    {
        $query = $this->entityManager()->createQuery('SELECT COUNT(*) FROM SaasOvation\Common\Event\StoredEvent s');

        return intval($query->getSingleScalarResult());
    }
}
