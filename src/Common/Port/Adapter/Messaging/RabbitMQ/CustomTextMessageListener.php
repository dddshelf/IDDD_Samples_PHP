<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ;

use DateTimeInterface;
use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\MessageListener\Type;

class CustomTextMessageListener extends MessageListener
{
    public function __construct(Type $aType, ExchangeListener $anExchangeListener)
    {
        $this->exchangeListener = $anExchangeListener;

        parent::__construct($aType);
    }

    public function handleMessage(
        $aType,
        $aMessageId,
        DateTimeInterface $aTimestamp,
        $aTextMessage,
        $aDeliveryTag,
        $isRedelivery
    ) {
        $this->exchangeListener->filteredDispatch($aType, $aTextMessage);
    }
}