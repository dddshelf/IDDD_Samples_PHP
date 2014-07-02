<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ;

use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\MessageListener\Type;
use Verraes\ClassFunctions\ClassFunctions;

/**
 * I am an abstract base class for exchange listeners.
 * I perform the basic set up according to the answers
 * from my concrete subclass.
 */
abstract class ExchangeListener
{
    /**
     * @var MessageConsumer
     */
    private $messageConsumer;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * Constructs my default state.
     */
    public function __construct()
    {
        $this->attachToQueue();
        
        $this->registerConsumer();
    }

    /**
     * Closes my queue.
     */
    public function close()
    {
        $this->queue()->close();
    }

    /**
     * Answers the String name of the exchange I listen to.
     *
     * @return String
     */
    abstract protected function exchangeName();

    /**
     * Filters out unwanted events and dispatches ones of interest.
     *
     * @param string $aType the String message type
     * @param string $aTextMessage the String raw text message being handled
     */
    abstract public function filteredDispatch($aType, $aTextMessage);

    /**
     * Answers the kinds of messages I listen to.
     *
     * @return string[]
     */
    abstract protected function listensTo();

    /**
     * Answers the String name of the queue I listen to. By
     * default it is the simple name of my concrete class.
     * May be overridden to change the name.
     *
     * @return string
     */
    protected function queueName()
    {
        return ClassFunctions::short($this);
    }

    /**
     * Attaches to the queues I listen to for messages.
     */
    private function attachToQueue()
    {
        $exchange = Exchange::fanOutInstance(
            ConnectionSettings::instance(),
            $this->exchangeName(),
            true
        );

        $this->queue = Queue::individualExchangeSubscriberInstance(
            $exchange,
            $this->exchangeName() . '.' . $this->queueName()
        );
    }

    /**
     * Answers my queue.
     *
     * @return Queue
     */
    private function queue()
    {
        return $this->queue;
    }

    /**
     * Registers my listener for queue messages and dispatching.
     */
    private function registerConsumer()
    {
        $this->messageConsumer = MessageConsumer::instance($this->queue(), false);

        $this->messageConsumer->receiveOnly(
            $this->listensTo(),
            new CustomTextMessageListener(new MessageListener\Type\Text(), $this)
        );
    }

    public function listenForPendingMessages($aNumberOfMessages = 1)
    {
        while ($aNumberOfMessages > 0) {
            $this->queue()->channel()->wait();
            $aNumberOfMessages--;
        }
    }
}
