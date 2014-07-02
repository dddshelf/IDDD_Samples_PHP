<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\View;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\Event\Sourcing\DispatchableDomainEvent;
use SaasOvation\Common\Event\Sourcing\EventDispatcher;

class MySQLProjectionDispatcher implements EventDispatcher
{
    /**
     * @var Collection
     */
    private $registeredProjections;

    public function __construct(EventDispatcher $aParentEventDispatcher)
    {
        $aParentEventDispatcher->registerEventDispatcher($this);
        
        $this->setRegisteredProjections(new ArrayCollection());
    }

    public function dispatch(DispatchableDomainEvent $aDispatchableDomainEvent)
    {
        foreach ($this->registeredProjections() as $projection) {
            $projection->dispatch($aDispatchableDomainEvent);
        }
    }

    public function registerEventDispatcher(EventDispatcher $aProjection)
    {
        $this->registeredProjections()->add($aProjection);
    }

    public function understands(DispatchableDomainEvent $aDispatchableDomainEvent)
    {
        return true;
    }

    private function registeredProjections()
    {
        return $this->registeredProjections;
    }

    private function setRegisteredProjections(Collection $aDispatchers)
    {
        $this->registeredProjections = $aDispatchers;
    }
}
