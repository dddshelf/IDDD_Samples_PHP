<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\Repository;

use Rhumsaa\Uuid\Uuid;
use SaasOvation\Collaboration\Domain\Model\Forum\Post;
use SaasOvation\Collaboration\Domain\Model\Forum\PostId;
use SaasOvation\Collaboration\Domain\Model\Forum\PostRepository;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Collaboration\Port\Adapter\Persistence\EventStoreProvider;
use SaasOvation\Common\Event\Sourcing\EventStreamId;

class EventStorePostRepository extends EventStoreProvider implements PostRepository
{
    public function postOfId(Tenant $aTenantId, PostId $aPostId)
    {
        $eventId = new EventStreamId($aTenantId->id() . ':' . $aPostId->id());

        $eventStream = $this->eventStore()->eventStreamSince($eventId);

        $post = new Post($eventStream->events(), $eventStream->version());

        return $post;
    }

    public function nextIdentity()
    {
        return new PostId(
            strtoupper(Uuid::uuid4())
        );
    }

    public function save(Post $aPost)
    {
        $streamName = $aPost->tenant()->id() . ':' . $aPost->postId()->id();

        $eventId = new EventStreamId(
            $streamName,
            $aPost->mutatedVersion()
        );

        $this->eventStore()->appendWith($eventId, $aPost->mutatingEvents());
    }
}
