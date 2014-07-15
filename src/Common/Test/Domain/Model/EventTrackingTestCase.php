<?php

namespace SaasOvation\Common\Test\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpCollection\Map;
use PHPUnit_Framework_TestCase;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Port\Adapter\Messaging\Slothmq\SlothServer;
use UnexpectedValueException;

abstract class EventTrackingTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var TestAgilePMRabbitMQExchangeListener
     */
    protected $agilePmRabbitMQExchangeListener;

    /**
     * @var TestAgilePMSlothMQExchangeListener
     */
    protected $agilePmSlothMQExchangeListener;

    /**
     * @var TestCollaborationRabbitMQExchangeListener
     */
    protected $collaborationRabbitMQExchangeListener;

    /**
     * @var TestCollaborationSlothMQExchangeListener
     */
    protected $collaborationSlothMQExchangeListener;

    /**
     * @var TestIdentityAccessRabbitMQExchangeListener
     */
    protected $identityAccessRabbitMQExchangeListener;

    /**
     * @var TestIdentityAccessSlothMQExchangeListener
     */
    protected $identityAccessSlothMQExchangeListener;

    /**
     * @var Collection
     */
    private $handledEvents;

    /**
     * @var Map
     */
    private $handledNotifications;

    protected function expectedEvent($aDomainEventType, $aTotal = 1)
    {
        $count = 0;
    
        foreach ($this->handledEvents as $type) {
            if ($type == $aDomainEventType) {
                ++$count;
            }
        }

        if ($count != $aTotal) {
            throw new UnexpectedValueException('Expected ' . $aTotal . ' ' . $aDomainEventType . ' events, but handled '
                . $this->handledEvents->size() . ' events: ' . $this->handledEvents);
        }
    }

    protected function expectedEvents($anEventCount)
    {
        if ($this->handledEvents->count() != $anEventCount) {
            throw new UnexpectedValueException('Expected ' . $anEventCount . ' events, but handled ' . $this->handledEvents->count()
                . ' events: ' . $this->handledEvents);
        }
    }

    protected function expectedNotification($aNotificationType, $aTotal = 1)
    {
        sleep(0.2);

        $count = 0;

        $notificationTypeName = $aNotificationType;

        foreach ($this->handledNotifications->values() as $type) {
            if ($type === $notificationTypeName) {
                ++$count;
            }
        }

        if ($count !== $aTotal) {
            throw new UnexpectedValueException('Expected ' . $aTotal . ' ' . $aNotificationType
                . ' notifications, but handled ' . $this->handledNotifications->count() . ' notifications: '
                . print_r($this->handledNotifications->values(), true)
            );
        }
    }

    protected function expectedNotifications($anNotificationCount)
    {
        sleep(0.2);

        if ($this->handledNotifications->count() != $anNotificationCount) {
            throw new UnexpectedValueException('Expected ' . $anNotificationCount . ' notifications, but handled '
                . $this->handledNotifications->count() . ' notifications: ' . print_r($this->handledNotifications->values(), true)
            );
        }
    }

    protected function setUp()
    {
        sleep(0.1);

        // SlothServer::executeInProcessDetachedServer();

        sleep(0.1);

        DomainEventPublisher::instance()->reset();

        $this->handledEvents = new ArrayCollection();
        $this->handledNotifications = new Map();

        DomainEventPublisher::instance()->subscribe(new DomainEventSubscriber($this->handledEvents));

        $this->agilePmRabbitMQExchangeListener          = new TestAgilePMRabbitMQExchangeListener($this);
        $this->collaborationRabbitMQExchangeListener    = new TestCollaborationRabbitMQExchangeListener($this);
        $this->identityAccessRabbitMQExchangeListener   = new TestIdentityAccessRabbitMQExchangeListener($this);

        $this->clearExchangeListeners();

        // $this->agilePmSlothMQExchangeListener = new TestAgilePMSlothMQExchangeListener();
        // $this->collaborationSlothMQExchangeListener = new TestCollaborationSlothMQExchangeListener();
        // $this->identityAccessSlothMQExchangeListener = new TestIdentityAccessSlothMQExchangeListener();

        sleep(0.2);
    }

    private function clearExchangeListeners()
    {
        // At beginning of the test, give MQExchangeListeners time to receive
        // messages from queues which were published by previous tests->
        // Since RabbitMQ Java Client does not allow queue listing or cleaning
        // all queues at once, we can just consume all messages and do
        // nothing with them as a work-around->

        sleep(0.5);

        $this->agilePmRabbitMQExchangeListener->clear();
        $this->collaborationRabbitMQExchangeListener->clear();
        $this->identityAccessRabbitMQExchangeListener->clear();
    }

    protected function tearDown()
    {
        $this->agilePmRabbitMQExchangeListener->close();
        $this->collaborationRabbitMQExchangeListener->close();
        $this->identityAccessRabbitMQExchangeListener->close();

        // $this->agilePmSlothMQExchangeListener->close();
        // $this->collaborationSlothMQExchangeListener->close();
        // $this->identityAccessSlothMQExchangeListener->close();
        //
        // SlothClient::instance()->closeAll();

        $this->handledEvents = $this->handledNotifications = null;
    }

    public function handledEvents()
    {
        return $this->handledEvents;
    }

    public function handledNotifications()
    {
        return $this->handledNotifications;
    }
}
