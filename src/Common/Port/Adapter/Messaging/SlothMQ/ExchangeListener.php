<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\SlothMQ;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class ExchangeListener
{
    /**
     * @var Collection
     */
    private $messageTypes;

    public function __construct()
    {
        $this->establishMessageTypes();
        
        SlothClient::instance()->register($this);
    }

    public function close()
    {
        SlothClient::instance()->unregister($this);
    }

    public abstract function exchangeName();

    protected abstract function filteredDispatch($aType, $aTextMessage);

    protected abstract function listensTo();

    protected function listensToType($aType)
    {
        $types = $this->listensToMessageTypes();

        return $types->isEmpty() || $types->contains($aType);
    }

    public abstract function name();

    private function establishMessageTypes()
    {
        $filterOutAllBut = $this->listensTo();

        if (null === $filterOutAllBut) {
            $filterOutAllBut = [];
        }

        $this->setMessageTypes(new ArrayCollection($filterOutAllBut));
    }

    private function listensToMessageTypes()
    {
        return $this->messageTypes;
    }

    private function setMessageTypes(Collection $aMessageTypes)
    {
        $this->messageTypes = $aMessageTypes;
    }
}
