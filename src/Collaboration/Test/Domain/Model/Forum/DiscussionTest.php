<?php

namespace SaasOvation\Collaboration\Test\Domain\Model\Forum;

use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Forum\Discussion;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionStarted;
use SaasOvation\Collaboration\Domain\Model\Forum\Forum;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumStarted;
use SaasOvation\Collaboration\Domain\Model\Forum\Post;
use SaasOvation\Collaboration\Domain\Model\Forum\PostedToDiscussion;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;

class DiscussionTest extends AbstractForumTest
{
    /**
     * @var Discussion
     */
    private $discussion;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var Post
     */
    private $post;

    /**
     * @var Post
     */
    private $postAgain;

    public function testPostToDiscussion()
    {
        $this->forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($this->forum);

        $aDiscussionStartedSubscriber = new DiscussionStartedSubscriber();
        DomainEventPublisher::instance()->subscribe($aDiscussionStartedSubscriber);

        $this->discussion = $this->forum->startDiscussion(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'All About DDD'
        );

        DomainRegistry::discussionRepository()->save($this->discussion);

        $this->assertNotNull($this->discussion);
        $this->assertNotNull($aDiscussionStartedSubscriber->getDiscussionId());

        $this->post = $this->discussion->postWithoutReply(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'All About DDD',
            'I\'d like to start a discussion all about doing domain-driven design.'
        );

        DomainRegistry::postRepository()->save($this->post);

        $this->assertNotNull($this->post);
        $this->assertEquals('jdoe', $this->post->author()->identity());
        $this->assertEquals('All About DDD', $this->post->subject());

        $this->expectedEvents(3);
        $this->expectedEvent(ForumStarted::class);
        $this->expectedEvent(DiscussionStarted::class);
        $this->expectedEvent(PostedToDiscussion::class);
    }

    public function testMultiplePostsToDiscussion()
    {
        $this->forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($this->forum);

        $aDiscussionStartedSubscriber = new DiscussionStartedSubscriber();
        DomainEventPublisher::instance()->subscribe($aDiscussionStartedSubscriber);

        $this->discussion = $this->forum->startDiscussion(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'All About DDD'
        );

        DomainRegistry::discussionRepository()->save($this->discussion);

        $aPostedToDiscussionSubscriber = new PostedToDiscussionSubscriber();
        DomainEventPublisher::instance()->subscribe($aPostedToDiscussionSubscriber);

        $this->post = $this->discussion->postWithoutReply(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'All About DDD',
            'I\'d like to start a discussion all about doing domain-driven design.'
        );

        $this->postAgain = $this->discussion->postWithoutReply(
            DomainRegistry::forumIdentityService(),
            new Author('bobw', 'Bob Williams', 'bobw@saasovation.com'),
            'RE: All About DDD',
            'Well, I think it\'s a great idea!'
        );

        DomainRegistry::postRepository()->save($this->post);
        DomainRegistry::postRepository()->save($this->postAgain);

        $tenant         = $aPostedToDiscussionSubscriber->getTenant();
        $forumId        = $aPostedToDiscussionSubscriber->getForumId();
        $discussionId   = $aPostedToDiscussionSubscriber->getDiscussionId();
        $subject        = $aPostedToDiscussionSubscriber->getSubject();
        $bodyText       = $aPostedToDiscussionSubscriber->getBodyText();

        $this->assertNotNull($tenant);
        $this->assertEquals($this->forum->tenant(), $tenant);
        $this->assertNotNull($forumId);
        $this->assertEquals($this->forum->forumId(), $forumId);
        $this->assertNotNull($discussionId);
        $this->assertEquals($this->discussion->discussionId(), $discussionId);
        $this->assertNotNull($subject);
        $this->assertNotNull($bodyText);

        $postId = $aPostedToDiscussionSubscriber->getPostId();

        $this->assertNotNull($postId);
        $this->assertEquals('RE: All About DDD', $this->postAgain->subject());
        $this->assertEquals('bobw', $this->postAgain->author()->identity());

        $this->expectedEvents(4);
        $this->expectedEvent(ForumStarted::class);
        $this->expectedEvent(DiscussionStarted::class);
        $this->expectedEvent(PostedToDiscussion::class, 2);
    }
}

