<?php

namespace SaasOvation\Common\Event\Sourcing;

use Doctrine\Common\Collections\Collection;

interface EventStore
{
    public function appendWith(EventStreamId $aStartingIdentity, Collection $anEvents);

    public function close();

    /**
     * @param $aLastReceivedEvent
     *
     * @return Collection
     */
    public function eventsSince($aLastReceivedEvent);

    /**
     * @param EventStreamId $anIdentity
     * @return EventStream
     */
    public function eventStreamSince(EventStreamId $anIdentity);

    public function fullEventStreamFor(EventStreamId $anIdentity);

    public function purge(); // mainly used for testing

    public function registerEventNotifiable(EventNotifiable $anEventNotifiable);
}
