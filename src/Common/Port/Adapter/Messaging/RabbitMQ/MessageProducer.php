<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ;

use Exception;
use InvalidArgumentException;
use PhpAmqpLib\Message\AMQPMessage;
use SaasOvation\Common\Port\Adapter\Messaging\MessageException;

/**
 * I am a message producer, which facilitates sending messages to a BrokerChannel.
 * A BrokerChannel may be either an Exchange or a Queue.
 *
 * @author Vaughn Vernon
 */
class MessageProducer
{
    /**
     * My brokerChannel, which is where I send messages->
     *
     * @var BrokerChannel
     */
    private $brokerChannel;

    /**
     * Answers a new instance of a MessageProducer->
     *
     * @param BrokerChannel $aBrokerChannel the BrokerChannel where messages are to be sent
     *
     * @return MessageProducer
     */
    public static function instance(BrokerChannel $aBrokerChannel)
    {
        return new MessageProducer($aBrokerChannel);
    }

    /**
     * Closes me, which closes my broker channel.
     */
    public function close()
    {
        $this->brokerChannel()->close();
    }

    /**
     * Answers the receiver after sending aTextMessage to my channel
     * with aMessageParameters as the message basic properties.
     * This is a producer ignorance way to use either an exchange or
     * a queue channel without requiring it to pass specific parameters.
     * By answering myself I allow for sending message bursts.
     *
     * @param string $aTextMessage the String text message to send
     * @param MessageParameters $aMessageParameters the MessageParameters
     *
     * @throws MessageException
     *
     * @return MessageProducer
     */
    public function send($aTextMessage, MessageParameters $aMessageParameters)
    {
        $this->check($aMessageParameters);

        $aMessage = new AMQPMessage(
            $aTextMessage,
            $aMessageParameters->properties()
        );

        try {
            $this->brokerChannel()->channel()->basic_publish(
                $aMessage,
                $this->brokerChannel()->exchangeName()
            );
        } catch (Exception $e) {
            throw new MessageException('Failed to send message to channel.', $e);
        }

        return $this;
    }

    /**
     * Constructs my default state.
     *
     * @param BrokerChannel $aBrokerChannel the BrokerChannel to which I send messages
     */
    protected function __construct(BrokerChannel $aBrokerChannel)
    {
        $this->setBrokerChannel($aBrokerChannel);
    }

    /**
     * Answers my brokerChannel.
     *
     * @return BrokerChannel
     */
    protected function brokerChannel()
    {
        return $this->brokerChannel;
    }

    /**
     * Sets my brokerChannel.
     *
     * @param BrokerChannel $aBrokerChannel the BrokerChannel to set as my brokerChannel
     */
    private function setBrokerChannel(BrokerChannel $aBrokerChannel)
    {
        $this->brokerChannel = $aBrokerChannel;
    }

    /**
     * Checks aMessageParameters for validity.
     * @param MessageParameters $aMessageParameters the MessageParameters to check
     */
    private function check(MessageParameters $aMessageParameters)
    {
        if ($this->brokerChannel()->isDurable()) {
            if (!$aMessageParameters->isDurable()) {
                throw new InvalidArgumentException('MessageParameters must be durable.');
            }
        } else {
            if ($aMessageParameters->isDurable()) {
                throw new InvalidArgumentException('MessageParameters must not be durable.');
            }
        }
    }

    /**
     * Answers the binary durability BasicProperties according
     * to the brokerChannel's durability.
     *
     * @return BasicProperties
     */
    private function binaryDurability()
    {
        if ($this->brokerChannel()->isDurable()) {
            return MessageProperties::PERSISTENT_BASIC;
        }
    }

    /**
     * Answers the text durability BasicProperties according
     * to the brokerChannel's durability.
     *
     * @return BasicProperties
     */
    private function textDurability()
    {
        if ($this->brokerChannel()->isDurable()) {
            return MessageProperties::PERSISTENT_TEXT_PLAIN;
        }
    }
}
