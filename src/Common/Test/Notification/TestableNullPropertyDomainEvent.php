<?php

namespace SaasOvation\Common\Test\Notification;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class TestableNullPropertyDomainEvent implements DomainEvent
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

    /**
     * @var int
     */
    private $numberMustBeNull;

    /**
     * @var string
     */
    private $textMustBeNull;

    /**
     * @var string
     */
    private $textMustBeNull2;

    /**
     * @var Nested
     */
    private $nested;

    /**
     * @var Nested
     */
    private $nullNested;

    public function __construct($anId, $aName)
    {
        $this->setId($anId);
        $this->setName($aName);
        $this->setOccurredOn(new DateTimeImmutable());
    
        $this->nested = new Nested();
        $this->nullNested = null;
    }

    public function id()
    {
        return $this->id;
    }

    public function nested()
    {
        return $this->nested;
    }

    public function nullNested()
    {
        return $this->nullNested;
    }

    public function numberMustBeNull()
    {
        return $this->numberMustBeNull;
    }

    public function textMustBeNull()
    {
        return $this->textMustBeNull;
    }

    public function textMustBeNull2()
    {
        return $this->textMustBeNull2;
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

class Nested
{
    /**
     * @var string
     */
    private $nestedTextMustBeNull;

    /**
     * @var NestedDeeply
     */
    private $nestedDeeply;

    /**
     * @var NestedDeeply
     */
    private $nullNestedDeeply;

    public function __construct()
    {
        $this->nestedDeeply = new NestedDeeply();
        $this->nullNestedDeeply = null;
    }

    public function nestedDeeply()
    {
        return $this->nestedDeeply;
    }

    public function nullNestedDeeply()
    {
        return $this->nullNestedDeeply;
    }

    public function nestedTextMustBeNull()
    {
        return $this->nestedTextMustBeNull;
    }
}

class NestedDeeply
{
    /**
     * @var string
     */
    private $nestedDeeplyTextMustBeNull;

    /**
     * @var string
     */
    private $nestedDeeplyTextMustBeNull2;

    public function nestedDeeplyTextMustBeNull()
    {
        return $this->nestedDeeplyTextMustBeNull;
    }

    public function nestedDeeplyTextMustBeNull2()
    {
        return $this->nestedDeeplyTextMustBeNull2;
    }
}
