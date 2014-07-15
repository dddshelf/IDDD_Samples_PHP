<?php

namespace SaasOvation\Common\Test\Event;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class TestableDomainEvent implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    public function __construct($anId, $aName)
    {
        $this->setId($anId);
        $this->setName($aName);
        $this->setOccurredOn(new DateTimeImmutable());
    }

    public function id()
    {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }

    private function setId($id)
    {
        $this->id = $id;
    }

    private function setName($name)
    {
        $this->name = $name;
    }

    private function setOccurredOn(DateTimeInterface $occurredOn)
    {
        $this->occurredOn = $occurredOn;
    }
}
