<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ;
use DateTimeInterface;
use RuntimeException;
use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\MessageListener\Type;

/**
 * I am a message listener, which is given each message received
 * by a MessageConsumer. I am also an adapter because I provide
 * defaults for both handleMessage() behaviors. A typical subclass
 * would override one or the other handleMessage() based on its
 * type and leave the remaining handleMessage() defaulted since
 * it will never be used by MessageConsumer.
 *
 * @author Vaughn Vernon
 */
abstract class MessageListener
{
    /**
     * My type, which indicates whether I listen for BINARY or TEXT messages.
     *
     * @var Type
     */
    private $type;

    /**
     * Constructs my default state.
     *
     * @param Type $aType Type of listener, either BINARY or TEXT
     */
    public function __construct(Type $aType)
    {
        $this->setType($aType);
    }

    /**
     * Handles aBinaryMessage. If any MessageException is thrown by
     * my implementor its isRetry() is examined and, if true, the
     * message being handled will be nack'd and re-queued. Otherwise,
     * if its isRetry() is false the message will be rejected/failed
     * (not re-queued). If any other Exception is thrown the message
     * will be considered not handled and is rejected/failed.
     *
     * @param string aType the String type of the message if sent, or null
     * @param string aMessageId the String id of the message if sent, or null
     * @param DateTimeInterface $aTimestamp the Date timestamp of the message if sent, or null
     * @param string $aBinaryMessage the byte[] containing the binary message
     * @param long $aDeliveryTag the long tag delivered with the message
     * @param boolean $isRedelivery the boolean indicating whether or not $this message is a redelivery
     *
     * @throws Exception when any problem occurs and the message must not be acknowledged
     */
    public function handleMessage(
        $aType,
        $aMessageId,
        DateTimeInterface $aTimestamp,
        $aBinaryMessage,
        $aDeliveryTag,
        $isRedelivery
    ) {
        throw new RuntimeException('Must be implemented by my subclass.');
    }

    /**
     * Answers my type.
     *
     * @return Type
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Sets my type.
     *
     * @param Type $aType the Type to set as my type
     */
    private function setType(Type $aType)
    {
        $this->type = $aType;
    }
}
