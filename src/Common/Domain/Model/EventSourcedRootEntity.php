<?php

namespace SaasOvation\Common\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use SaasOvation\Common\AssertionConcern;
use Verraes\ClassFunctions\ClassFunctions;

abstract class EventSourcedRootEntity extends AssertionConcern
{
    private static $MUTATOR_METHOD_NAME = 'when';

    /**
     * @var Collection
     */
    private $mutatingEvents;

    /**
     * @var int
     */
    private $unmutatedVersion;

    public function mutatedVersion()
    {
        return $this->unmutatedVersion() + 1;
    }

    public function mutatingEvents()
    {
        return $this->mutatingEvents;
    }

    public function unmutatedVersion()
    {
        return $this->unmutatedVersion;
    }

    public function __construct($anEventStream = null, $aStreamVersion = null)
    {
        $this->mutatingEvents = new ArrayCollection();
        $this->mutate($anEventStream, $aStreamVersion);
    }

    protected function apply(DomainEvent $aDomainEvent)
    {
        $this->mutatingEvents()->add($aDomainEvent);

        $this->mutateWhen($aDomainEvent);
    }

    protected function mutateWhen(DomainEvent $aDomainEvent)
    {
        $mutatorMethod = self::$MUTATOR_METHOD_NAME . ClassFunctions::short($aDomainEvent);

        if (!method_exists($this, $mutatorMethod)) {
            throw new InvalidArgumentException(sprintf('The event "%s" is not supported', get_class($aDomainEvent)));
        }

        $this->$mutatorMethod($aDomainEvent);
    }

    private function setUnmutatedVersion($aStreamVersion)
    {
        $this->unmutatedVersion = $aStreamVersion;
    }

    protected function mutate(Collection $anEventStream = null, $aStreamVersion = null)
    {
        if (null === $anEventStream && null === $aStreamVersion) {
            return;
        }

        foreach ($anEventStream as $event) {
            $this->mutateWhen($event);
        }

        $this->setUnmutatedVersion($aStreamVersion);
    }
}
