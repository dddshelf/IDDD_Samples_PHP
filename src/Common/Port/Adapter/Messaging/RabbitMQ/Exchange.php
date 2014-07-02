<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ;

use Exception;
use SaasOvation\Common\Port\Adapter\Messaging\MessageException;

class Exchange extends BrokerChannel
{
    /**
     * My type, which is the type of exchange.
     * 
     * @var string
     */
    private $type;

    /**
     * Answers a new instance of a direct Exchange with the name aName. The
     * underlying exchange has the isDurable quality, and is not auto-deleted.
     *
     * @param ConnectionSettings $aConnectionSettings the ConnectionSettings
     * @param string $aName the String name of the exchange
     * @param boolean $isDurable the boolean indicating whether or not I am durable
     *
     * @return Exchange
     */
    public static function directInstance(ConnectionSettings $aConnectionSettings, $aName, $isDurable)
    {
        return Exchange::create($aConnectionSettings, $aName, 'direct', $isDurable);
    }

    /**
     * Answers a new instance of a fan-out Exchange with the name aName. The
     * underlying exchange has the isDurable quality, and is not auto-deleted.
     *
     * @param ConnectionSettings $aConnectionSettings the ConnectionSettings
     * @param string $aName the String name of the exchange
     * @param boolean $isDurable the boolean indicating whether or not I am durable
     *
     * @return Exchange
     */
    public static function fanOutInstance(ConnectionSettings $aConnectionSettings, $aName, $isDurable)
    {
        return Exchange::create($aConnectionSettings, $aName, 'fanout', $isDurable);
    }

    /**
     * Answers a new instance of a headers Exchange with the name aName. The
     * underlying exchange has the isDurable quality, and is not auto-deleted.
     *
     * @param ConnectionSettings $aConnectionSettings the ConnectionSettings
     * @param string $aName the String name of the exchange
     * @param boolean $isDurable the boolean indicating whether or not I am durable
     *
     * @return Exchange
     */
    public static function headersInstance(ConnectionSettings $aConnectionSettings, $aName, $isDurable)
    {
        return Exchange::create($aConnectionSettings, $aName, 'headers', $isDurable);
    }

    /**
     * Answers a new instance of a topic Exchange with the name aName. The
     * underlying exchange has the isDurable quality, and is not auto-deleted.
     *
     * @param ConnectionSettings $aConnectionSettings the ConnectionSettings
     * @param string $aName the String name of the exchange
     * @param boolean $isDurable the boolean indicating whether or not I am durable
     *
     * @return Exchange
     */
    public static function topicInstance(ConnectionSettings $aConnectionSettings, $aName, $isDurable)
    {
        return Exchange::create($aConnectionSettings, $aName, 'topic', $isDurable);
    }

    public static function create(ConnectionSettings $aConnectionSettings, $aName, $aType, $isDurable)
    {
        $anInstance = parent::namedBrokerFromConnectionSettings($aConnectionSettings, $aName);

        $anInstance->setDurable($isDurable);
        $anInstance->setType($aType);

        try {
            $anInstance->channel()->exchange_declare($aName, $aType, false, $isDurable, true);
        } catch (Exception $e) {
            throw new MessageException('Failed to create/open the exchange.', $e);
        }

        return $anInstance;
    }

    /**
     * @see \SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\BrokerChannel#isExchange()
     */
    protected function isExchange()
    {
        return true;
    }

    /**
     * Answers my type.
     *
     * @return string
     */
    protected function type()
    {
        return $this->type;
    }

    /**
     * Sets my type.
     *
     * @param string $aType the String to set as my type
     */
    private function setType($aType)
    {
        $this->type = $aType;
    }
}