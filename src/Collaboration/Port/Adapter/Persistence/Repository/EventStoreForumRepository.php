<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\Repository;

use Rhumsaa\Uuid\Uuid;
use SaasOvation\Collaboration\Domain\Model\Forum\Forum;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumId;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumRepository;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Collaboration\Port\Adapter\Persistence\EventStoreProvider;
use SaasOvation\Common\Event\Sourcing\EventStreamId;

class EventStoreForumRepository extends EventStoreProvider implements ForumRepository
{
    public function forumOfId(Tenant $aTenant, ForumId $aForumId)
    {
        $eventId = new EventStreamId($aTenant->id() . ':' . $aForumId->id());

        $eventStream = $this->eventStore()->eventStreamSince($eventId);

        $forum = new Forum($eventStream->events(), $eventStream->version());

        return $forum;
    }

    public function nextIdentity()
    {
        return new ForumId(
            strtoupper(Uuid::uuid4())
        );
    }

    public function save(Forum $aForum)
    {
        $streamVersion = $aForum->tenant()->id() . ':' . $aForum->forumId()->id();

        $eventId = new EventStreamId(
            $streamVersion,
            $aForum->mutatedVersion()
        );

        $this->eventStore()->appendWith($eventId, $aForum->mutatingEvents());
    }
}
