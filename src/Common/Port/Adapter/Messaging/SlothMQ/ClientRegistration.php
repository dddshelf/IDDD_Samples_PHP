<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\SlothMQ;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ClientRegistration
{
    /**
     * @var Collection
     */
    private $exchanges;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var int
     */
    private $port;

    public function __construct($aPort, $anIPAddress = null)
    {
        $this->exchanges = new ArrayCollection();
        $this->ipAddress = null === $anIPAddress ? '0.0.0.0' : $anIPAddress;
        $this->port = $aPort;
    }

    public function addSubscription($anExchangeName)
    {
        echo 'ADDING EXCHANGE: ' . $anExchangeName;
        $this->exchanges->add($anExchangeName);
    }

    public function matches($anIPAddress, $aPort)
    {
        return $anIPAddress === $this->ipAddress && $aPort === $this->port;
    }

    public function isSubscribedTo($anExchangeName)
    {
        return $this->exchanges->contains($anExchangeName);
    }

    public function ipAddress()
    {
        return $this->ipAddress;
    }

    public function port()
    {
        return $this->port;
    }

    public function removeSubscription($anExchangeName)
    {
        $this->exchanges->remove($anExchangeName);
    }

    public function __toString()
    {
        return 'ClientRegistration [ipAddress=' . $this->ipAddress . ', port='
        . $this->port . ', exchanges=' . $this->exchanges . "]";
    }
}
