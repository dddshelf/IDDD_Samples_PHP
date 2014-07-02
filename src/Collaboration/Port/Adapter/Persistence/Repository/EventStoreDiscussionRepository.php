<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\Repository;

use Rhumsaa\Uuid\Uuid;
use SaasOvation\Collaboration\Domain\Model\Forum\Discussion;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionId;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionRepository;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Collaboration\Port\Adapter\Persistence\EventStoreProvider;
use SaasOvation\Common\Event\Sourcing\EventStreamId;

class EventStoreDiscussionRepository extends EventStoreProvider implements DiscussionRepository
{
    public function discussionOfId(Tenant $aTenant, DiscussionId $aDiscussionId)
    {
        $eventId = new EventStreamId($aTenant->id() . ':' . $aDiscussionId->id());

        $eventStream = $this->eventStore()->eventStreamSince($eventId);

        $Discussion = new Discussion($eventStream->events(), $eventStream->version());

        return $Discussion;
    }

    public function nextIdentity()
    {
        return new DiscussionId(strtoupper(Uuid::uuid4()));
    }

    public function save(Discussion $aDiscussion)
    {
        $streamName = $aDiscussion->tenant()->id() . ':' . $aDiscussion->discussionId()->id();

        $eventId = new EventStreamId(
            $streamName,
            $aDiscussion->mutatedVersion()
        );

        $this->eventStore()->appendWith($eventId, $aDiscussion->mutatingEvents());
    }
}
