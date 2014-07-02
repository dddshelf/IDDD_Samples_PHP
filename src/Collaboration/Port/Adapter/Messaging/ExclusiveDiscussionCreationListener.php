<?php

namespace SaasOvation\Collaboration\Port\Adapter\Messaging;

use SaasOvation\Collaboration\Application\Forum\ForumApplicationService;
use SaasOvation\Common\Notification\NotificationReader;
use SaasOvation\Common\Port\Adapter\Messaging\Exchanges;
use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\ExchangeListener;

class ExclusiveDiscussionCreationListener extends ExchangeListener
{
    /**
     * @var ForumApplicationService
     */
    private $forumApplicationService;

    protected function exchangeName()
    {
        return Exchanges::$COLLABORATION_EXCHANGE_NAME;
    }

    protected function filteredDispatch($aType, $aTextMessage)
    {
        $reader = new NotificationReader($aTextMessage);

        $tenantId = $reader->eventStringValue('tenantId');
        $exclusiveOwnerId = $reader->eventStringValue('exclusiveOwnerId');
        $creatorId = $reader->eventStringValue('creatorId');
        $moderatorId = $reader->eventStringValue('moderatorId');
        $authorId = $reader->eventStringValue('authorId');
        $forumSubject = $reader->eventStringValue('forumTitle');
        $forumDescription = $reader->eventStringValue('forumDescription');
        $discussionSubject = $reader->eventStringValue('discussionSubject');

        $this->forumApplicationService->startExclusiveForumWithDiscussion(
            $tenantId,
            $exclusiveOwnerId,
            $creatorId,
            $moderatorId,
            $authorId,
            $forumSubject,
            $forumDescription,
            $discussionSubject,
            null
        );
    }

    protected function listensTo()
    {
        return [
            'com.saasovation.collaboration.discussion.CreateExclusiveDiscussion'
        ];
    }
}
