<?php

namespace SaasOvation\Collaboration\Test\Application\Forum;

use SaasOvation\Collaboration\Test\Application\ApplicationTest;
use SaasOvation\Collaboration\Application\Forum\Data\DiscussionCommandResult;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Domain\Model\Forum\PostId;

class DiscussionApplicationServiceTest extends ApplicationTest
{
    public function testCloseDiscussion()
    {
        $forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($forum);

        $discussion = $this->discussionAggregate($forum);

        $this->assertFalse($discussion->isClosed());

        DomainRegistry::discussionRepository()->save($discussion);

        $this->discussionApplicationService->closeDiscussion(
            $discussion->tenant()->id(),
            $discussion->discussionId()->id()
        );

        $closedDiscussion = DomainRegistry::discussionRepository()->discussionOfId(
            $discussion->tenant(),
            $discussion->discussionId()
        );

        $this->assertNotNull($closedDiscussion);
        $this->assertTrue($closedDiscussion->isClosed());
    }

    public function testPostToDiscussion()
    {
        $forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($forum);

        $discussion = $this->discussionAggregate($forum);

        DomainRegistry::discussionRepository()->save($discussion);

        $result = new CustomDiscussionCommandResult();

        $this->discussionApplicationService->postToDiscussion(
            $discussion->tenant()->id(),
            $discussion->discussionId()->id(),
            'authorId1',
            'Post Test',
            'Post test text...',
            $result
        );

        $post = DomainRegistry::postRepository()->postOfId(
            $discussion->tenant(),
            new PostId($result->getPostId())
        );

        $this->assertNotNull($result->getDiscussionId());
        $this->assertNotNull($post);
        $this->assertEquals('authorId1', $post->author()->identity());
        $this->assertEquals('Post Test', $post->subject());
        $this->assertEquals('Post test text...', $post->bodyText());
    }

    public function testPostToDiscussionInReplyTo()
    {
        $forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($forum);

        $discussion = $this->discussionAggregate($forum);

        DomainRegistry::discussionRepository()->save($discussion);

        $result = new CustomDiscussionCommandResult();

        $this->discussionApplicationService->postToDiscussion(
            $discussion->tenant()->id(),
            $discussion->discussionId()->id(),
            'authorId1',
            'Post Test',
            'Post test text...',
            $result
        );

        $this->discussionApplicationService->postToDiscussionInReplyTo(
            $discussion->tenant()->id(),
            $discussion->discussionId()->id(),
            $result->getPostId(),
            'authorId2',
            'Post In Reply To Test',
            'Post test text in reply to...',
            $result
        );

        $postedInReplyTo = DomainRegistry::postRepository()->postOfId(
            $discussion->tenant(),
            new PostId($result->getPostId())
        );

        $this->assertNotNull($result->getDiscussionId());
        $this->assertNotNull($result->getInReplyToPostId());
        $this->assertNotNull($postedInReplyTo);
        $this->assertEquals('authorId2', $postedInReplyTo->author()->identity());
        $this->assertEquals('Post In Reply To Test', $postedInReplyTo->subject());
        $this->assertEquals('Post test text in reply to...', $postedInReplyTo->bodyText());
    }

    public function testReopenDiscussion()
    {
        $forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($forum);

        $discussion = $this->discussionAggregate($forum);

        $discussion->close();

        $this->assertTrue($discussion->isClosed());

        DomainRegistry::discussionRepository()->save($discussion);

        $this->discussionApplicationService->reopenDiscussion(
            $discussion->tenant()->id(),
            $discussion->discussionId()->id()
        );

        $openDiscussion = DomainRegistry::discussionRepository()->discussionOfId(
            $discussion->tenant(),
            $discussion->discussionId()
        );

        $this->assertNotNull($openDiscussion);
        $this->assertFalse($openDiscussion->isClosed());
    }
}
