<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use ReflectionClass;
use SaasOvation\Common\Port\Adapter\Messaging\MessageException;

/**
 * I am an abstract base class for all channels to
 * the RabbitMQ message broker.
 *
 * @author Vaughn Vernon
 */
abstract class BrokerChannel
{
    /**
     * My channel.
     *
     * @var AMQPChannel
     */
    private $channel;

    /**
     * My connection, which is the connection to my host broker.
     *
     * @var AMQPConnection
     */
    private $connection;

    /**
     * My durable property, which indicates whether or not messages are durable.
     *
     * @var boolean
     */
    private $durable;

    /**
     * My host, which is the host of the broker. There may be a :port appended.
     *
     * @var string
     */
    private $host;

    /**
     * My name.
     *
     * @var string
     */
    private $name;

    /**
     * Answers my host.
     *
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * Answers my name.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    public static function fromConnectionSettings(ConnectionSettings $aConnectionSettings)
    {
        return static::namedBrokerFromConnectionSettings(
            $aConnectionSettings,
            null
        );
    }

    /**
     * @param ConnectionSettings $aConnectionSettings
     * @param $aName
     *
     * @return static
     */
    public static function namedBrokerFromConnectionSettings(ConnectionSettings $aConnectionSettings, $aName)
    {
        $instance = new static();

        $connection = $instance->configureConnectionUsing($aConnectionSettings);

        $instance->setName($aName);

        try {
            $instance->setConnection($connection);

            $instance->setChannel($connection->channel());

            return $instance;

        } catch (Exception $e) {
            throw new MessageException('Failed to create/open the queue.', $e);
        }
    }

    public static function fromExistingBrokerChannel(BrokerChannel $aBrokerChannel)
    {
        return static::namedBrokerFromExistingBrokerChannel($aBrokerChannel, null);
    }

    /**
     * @param $aBrokerChannel
     * @param $aName
     *
     * @return static
     */
    public static function namedBrokerFromExistingBrokerChannel($aBrokerChannel, $aName)
    {
        $instance = new static();

        $instance->setHost($aBrokerChannel->host());
        $instance->setName($aName);
        $instance->setConnection($aBrokerChannel->connection());
        $instance->setChannel($aBrokerChannel->channel());

        return $instance;
    }

    /**
     * Answers my channel.
     *
     * @return AMQPChannel
     */
    public function channel()
    {
        return $this->channel;
    }

    /**
     * Answers my connection.
     *
     * @return AMQPConnection
     */
    private function connection()
    {
        return $this->connection;
    }

    /**
     * Closes me.
     */
    public function close()
    {
        // RabbitMQ doesn't guarantee that if isOpen()
        // answers true that close() will work because
        // another client may be racing to close the
        // same process and/or components-> so here just
        // attempt to close, catch and ignore, and move
        // on to next steps is the recommended approach->
        //
        // for the purpose here, the isOpen() checks prevent
        // closing a shared channel and connection that is
        // shared by a subscriber exchange and queue->

        try {
            if (null !== $this->channel()) {
                $this->channel()->close();
            }
        } catch (Exception $e) {
            // fall through
        }

        try {
            if (null !== $this->connection()) {
                $this->connection()->close();
            }
        } catch (Exception $e) {
            // fall through
        }

        $this->channel = null;
        $this->connection = null;
    }

    /**
     * Answers a new AMQPConnection configured with aConnectionSettings.
     *
     * @param ConnectionSettings $aConnectionSettings
     *
     * @return AMQPConnection
     */
    protected function configureConnectionUsing(ConnectionSettings $aConnectionSettings)
    {
        $reflectedClass = new ReflectionClass('\PhpAmqpLib\Connection\AMQPConnection');

        return $reflectedClass->newInstanceArgs([
            $aConnectionSettings->hostName(),
            $aConnectionSettings->hasPort() ? $aConnectionSettings->port() : null,
            $aConnectionSettings->hasUserCredentials() ? $aConnectionSettings->username() : null,
            $aConnectionSettings->hasUserCredentials() ? $aConnectionSettings->password() : null,
            $aConnectionSettings->virtualHost()
        ]);
    }

    /**
     * Answers whether or not I am durable.
     *
     * @return boolean
     */
    public function isDurable()
    {
        return $this->durable;
    }

    /**
     * Sets my durable.
     *
     * @param boolean $aDurable the boolean to set as my durable
     */
    protected function setDurable($aDurable)
    {
        $this->durable = $aDurable;
    }

    /**
     * Answers whether or not I am an exchange channel.
     *
     * @return boolean
     */
    protected function isExchange()
    {
        return false;
    }

    /**
     * Answers my name as the exchange name if I am
     * an Exchange; otherwise the empty String.
     *
     * @return string
     */
    public function exchangeName()
    {
        return $this->isExchange() ? $this->name() : '';
    }

    /**
     * Answers whether or not I am a queue channel.
     *
     * @return boolean
     */
    protected function isQueue()
    {
        return false;
    }

    /**
     * Answers my name as the queue name if I am
     * a Queue; otherwise the empty string.
     *
     * @return string
     */
    public function queueName()
    {
        return $this->isQueue() ? $this->name() : '';
    }

    /**
     * Sets my name.
     *
     * @param string $aName the string to set as my name
     */
    protected function setName($aName)
    {
        $this->name = $aName;
    }

    /**
     * Sets my channel.
     *
     * @param AMQPChannel $aChannel the Channel to set as my channel
     */
    private function setChannel(AMQPChannel $aChannel)
    {
        $this->channel = $aChannel;
    }

    /**
     * Sets my connection.
     *
     * @param AMQPConnection $aConnection the connection to set as my connection
     */
    private function setConnection(AMQPConnection $aConnection)
    {
        $this->connection = $aConnection;
    }

    /**
     * Sets my host.
     *
     * @param string $aHost the string to set as my host
     */
    private function setHost($aHost)
    {
        $this->host = $aHost;
    }
}
