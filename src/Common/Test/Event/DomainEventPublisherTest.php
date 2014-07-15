<?php

namespace SaasOvation\Common\Test\Event;

use PHPUnit_Framework_TestCase;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;

class DomainEventPublisherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var boolean
     */
    private $anotherEventHandled = false;

    /**
     * @var boolean
     */
    private $eventHandled = false;

    public function testDomainEventPublisherPublish()
    {
        DomainEventPublisher::instance()->reset();

        DomainEventPublisher::instance()->subscribe(new TestableDomainEventSubscriber($this));

        $this->assertFalse($this->eventHandled);

        DomainEventPublisher::instance()->publish(new TestableDomainEvent(100, 'test'));

        $this->assertTrue($this->eventHandled);
    }

    public function testDomainEventPublisherBlocked()
    {
        DomainEventPublisher::instance()->reset();

        DomainEventPublisher::instance()->subscribe(new TestableDomainEventSubscriber($this, true));

        DomainEventPublisher::instance()->subscribe(new AnotherTestableDomainEventSubscriber($this));

        $this->assertFalse($this->eventHandled);
        $this->assertFalse($this->anotherEventHandled);

        DomainEventPublisher::instance()->publish(new TestableDomainEvent(100, 'test'));

        $this->assertTrue($this->eventHandled);
        $this->assertFalse($this->anotherEventHandled);
    }

    public function setEventHandled($isEventHandled)
    {
        $this->eventHandled = $isEventHandled;
    }

    public function setAnotherEventHandled($anotherEventHandled)
    {
        $this->anotherEventHandled = $anotherEventHandled;
    }
}

class TestableDomainEventSubscriber implements DomainEventSubscriber
{
    /**
     * @var DomainEventPublisherTest
     */
    private $domainEventPublisherTest;

    /**
     * @var boolean
     */
    private $tryToPublishAnotherDomainEvent;

    function __construct($domainEventPublisherTest, $tryToPublishAnotherDomainEvent = false)
    {
        $this->domainEventPublisherTest = $domainEventPublisherTest;
        $this->tryToPublishAnotherDomainEvent = $tryToPublishAnotherDomainEvent;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->domainEventPublisherTest->assertEquals(100, $aDomainEvent->id());
        $this->domainEventPublisherTest->assertEquals('test', $aDomainEvent->name());
        $this->domainEventPublisherTest->setEventHandled(true);

        if ($this->tryToPublishAnotherDomainEvent) {
            DomainEventPublisher::instance()->publish(new AnotherTestableDomainEvent(1000.0));
        }
    }

    public function subscribedToEventType()
    {
        return TestableDomainEvent::class;
    }
}

class AnotherTestableDomainEventSubscriber implements DomainEventSubscriber
{
    /**
     * @var DomainEventPublisherTest
     */
    private $domainEventPublisherTest;

    function __construct($domainEventPublisherTest)
    {
        $this->domainEventPublisherTest = $domainEventPublisherTest;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        // should never be reached due to blocked publisher
        assertEquals(1000.0, $aDomainEvent->value());
        $this->domainEventPublisherTest->setAnotherEventHandled(true);
    }

    public function subscribedToEventType()
    {
        return AnotherTestableDomainEvent::class;
    }
}