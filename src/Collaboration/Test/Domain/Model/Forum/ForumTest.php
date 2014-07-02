<?php

namespace SaasOvation\Collaboration\Test\Domain\Model\Forum;

use Exception;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Moderator;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionStarted;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumClosed;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumDescriptionChanged;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumModeratorChanged;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumReopened;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumStarted;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumSubjectChanged;
use SaasOvation\Collaboration\Domain\Model\Forum\PostContentAltered;
use SaasOvation\Collaboration\Domain\Model\Forum\PostedToDiscussion;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;

class ForumTest extends AbstractForumTest
{
    public function testCreateForum()
    {
        $forum = $this->forumAggregate();

        $this->assertNotNull($forum->tenant());
        $this->assertNotNull($forum->tenant()->id());
        $this->assertEquals(8, strlen($forum->tenant()->id()));
        $this->assertEquals('jdoe', $forum->creator()->identity());
        $this->assertEquals('jdoe@saasovation.com', $forum->moderator()->emailAddress());
        $this->assertTrue($forum->isModeratedBy($forum->moderator()));
        $this->assertEquals('John Doe Does DDD', $forum->subject());
        $this->assertEquals('A set of discussions about DDD for anonymous developers.', $forum->description());

        DomainRegistry::forumRepository()->save($forum);

        $this->expectedEvents(1);
        $this->expectedEvent(ForumStarted::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages();

        $this->expectedNotifications(1);
        $this->expectedNotification(ForumStarted::class);
    }

    public function testAssignModerator()
    {
        $forum = $this->forumAggregate();

        $forum->assignModerator(new Moderator('zdoe', 'Zoe Doe', 'zdoe@saasovation.com'));

        $this->assertEquals('zdoe', $forum->moderator()->identity());
        $this->assertEquals('zdoe@saasovation.com', $forum->moderator()->emailAddress());

        DomainRegistry::forumRepository()->save($forum);

        $this->expectedEvents(2);
        $this->expectedEvent(ForumStarted::class);
        $this->expectedEvent(ForumModeratorChanged::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(2);

        $this->expectedNotifications(2);
        $this->expectedNotification(ForumStarted::class);
        $this->expectedNotification(ForumModeratorChanged::class);
    }

    public function testChangeDescription()
    {
        $forum = $this->forumAggregate();

        $forum->changeDescription("And Zoe knows...");

        $this->assertEquals("And Zoe knows...", $forum->description());

        DomainRegistry::forumRepository()->save($forum);

        $this->expectedEvents(2);
        $this->expectedEvent(ForumStarted::class);
        $this->expectedEvent(ForumDescriptionChanged::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(2);

        $this->expectedNotifications(2);
        $this->expectedNotification(ForumStarted::class);
        $this->expectedNotification(ForumDescriptionChanged::class);
    }

    public function testChangeSubject()
    {
        $forum = $this->forumAggregate();

        $forum->changeSubject('Zoe Likes DDD');

        $this->assertEquals('Zoe Likes DDD', $forum->subject());

        DomainRegistry::forumRepository()->save($forum);

        $this->expectedEvents(2);
        $this->expectedEvent(ForumStarted::class);
        $this->expectedEvent(ForumSubjectChanged::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(2);

        $this->expectedNotifications(2);
        $this->expectedNotification(ForumStarted::class);
        $this->expectedNotification(ForumSubjectChanged::class);
    }

    public function testClose()
    {
        $forum = $this->forumAggregate();

        $forum->close();

        $this->assertTrue($forum->isClosed());

        $failed = false;

        try {
            $forum->changeDescription('Blah...');

            $this->fail('Should have thrown exception.');

        } catch (Exception $e) {
            $failed = true;
        }

        $this->assertTrue($failed);

        DomainRegistry::forumRepository()->save($forum);

        $this->expectedEvents(2);
        $this->expectedEvent(ForumStarted::class);
        $this->expectedEvent(ForumClosed::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(2);

        $this->expectedNotifications(2);
        $this->expectedNotification(ForumStarted::class);
        $this->expectedNotification(ForumClosed::class);
    }

    public function testReopen()
    {
        $forum = $this->forumAggregate();

        $forum->close();

        $this->assertTrue($forum->isClosed());

        $forum->reopen();

        $this->assertFalse($forum->isClosed());

        try {
            $forum->changeDescription('Blah...');

        } catch (Exception $e) {
            $this->fail('Should have succeeded.');
        }

        $this->assertEquals('Blah...', $forum->description());

        DomainRegistry::forumRepository()->save($forum);

        $this->expectedEvents(4);
        $this->expectedEvent(ForumStarted::class);
        $this->expectedEvent(ForumClosed::class);
        $this->expectedEvent(ForumReopened::class);
        $this->expectedEvent(ForumDescriptionChanged::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(4);

        $this->expectedNotifications(4);
        $this->expectedNotification(ForumStarted::class);
        $this->expectedNotification(ForumClosed::class);
        $this->expectedNotification(ForumReopened::class);
        $this->expectedNotification(ForumDescriptionChanged::class);
    }

    public function testStartDiscussion()
    {
        $forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($forum);

        $aDiscussionStartedSubscriber = new DiscussionStartedSubscriber();
        DomainEventPublisher::instance()->subscribe($aDiscussionStartedSubscriber);

        $discussion = $forum->startDiscussion(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'All About DDD'
        );

        DomainRegistry::discussionRepository()->save($discussion);

        $tenant         = $aDiscussionStartedSubscriber->getTenant();
        $forumId        = $aDiscussionStartedSubscriber->getForumId();
        $discussionId   = $aDiscussionStartedSubscriber->getDiscussionId();
        $subject        = $aDiscussionStartedSubscriber->getSubject();

        $this->assertNotNull($tenant);
        $this->assertEquals($tenant, $forum->tenant());
        $this->assertNotNull($forumId);
        $this->assertEquals($forumId, $forum->forumId());
        $this->assertNotNull($discussionId);
        $this->assertNotNull($discussion);
        $this->assertEquals($discussionId, $discussion->discussionId());
        $this->assertEquals('jdoe', $discussion->author()->identity());
        $this->assertNotNull($subject);

        $this->expectedEvents(2);
        $this->expectedEvent(ForumStarted::class);
        $this->expectedEvent(DiscussionStarted::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(2);

        $this->expectedNotifications(2);
        $this->expectedNotification(ForumStarted::class);
        $this->expectedNotification(DiscussionStarted::class);
    }

    public function testModeratedPostContent()
    {
        $forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($forum);

        $aDiscussionStartedSubscriber = new DiscussionStartedSubscriber();
        DomainEventPublisher::instance()->subscribe($aDiscussionStartedSubscriber);

        $discussion = $forum->startDiscussion(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'All About DDD'
        );

        DomainRegistry::discussionRepository()->save($discussion);

        $post = $discussion->postWithoutReply(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'Subject',
            'Body text.'
        );

        DomainRegistry::postRepository()->save($post);

        $post = DomainRegistry::postRepository()->postOfId($post->tenant(), $post->postId());

        $forum->moderatePost(
            $post,
            $forum->moderator(),
            'MODERATED: Subject',
            'MODERATED: Body text.'
        );

        DomainRegistry::postRepository()->save($post);

        $this->assertStringStartsWith('MODERATED: ', $post->subject());
        $this->assertStringStartsWith('MODERATED: ', $post->bodyText());

        $this->expectedEvents(4);
        $this->expectedEvent(ForumStarted::class);
        $this->expectedEvent(DiscussionStarted::class);
        $this->expectedEvent(PostedToDiscussion::class);
        $this->expectedEvent(PostContentAltered::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(4);

        $this->expectedNotifications(4);
        $this->expectedNotification(ForumStarted::class);
        $this->expectedNotification(DiscussionStarted::class);
        $this->expectedNotification(PostedToDiscussion::class);
        $this->expectedNotification(PostContentAltered::class);
    }
}
