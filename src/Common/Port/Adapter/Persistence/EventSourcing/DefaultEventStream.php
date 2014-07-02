<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\EventSourcing;

use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\Event\Sourcing\EventStream;

class DefaultEventStream implements EventStream
{
    /**
     * @var Collection
     */
    private $events;

    /**
     * @var int
     */
    private $version;

    public function __construct(Collection $anEventsList, $aVersion)
    {
        $this->setEvents($anEventsList);
        $this->setVersion($aVersion);
    }

    public function events()
    {
        return $this->events;
    }

    public function version()
    {
        return $this->version;
    }

    private function setEvents(Collection $anEventsList)
    {
        $this->events = $anEventsList;
    }

    private function setVersion($aVersion)
    {
        $this->version = $aVersion;
    }
}
