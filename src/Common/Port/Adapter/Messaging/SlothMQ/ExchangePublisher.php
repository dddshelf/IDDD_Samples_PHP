<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\SlothMQ;

class ExchangePublisher
{
    /**
     * @var string
     */
    private $exchangeName;

    public function __construct($anExchangeName)
    {
        $this->exchangeName = $anExchangeName;
    }

    public function publish($aType, $aMessage)
    {
        SlothClient::instance()->publish($this->exchangeName(), $aType, $aMessage);
    }

    private function exchangeName()
    {
        return $this->exchangeName;
    }
}
