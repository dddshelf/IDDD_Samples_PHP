<?php

namespace SaasOvation\Collaboration\Test\Application\Forum;

use SaasOvation\Collaboration\Test\Application\ApplicationTest;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;

class DiscussionQueryServiceTest extends ApplicationTest
{
    public function testAllDiscussionsDataOfForum()
    {
        $forum = $this->forumAggregate();
        DomainRegistry::forumRepository()->save($forum);

        $discussions = $this->discussionAggregates($forum);

        foreach ($discussions as $discussion) {
            DomainRegistry::discussionRepository()->save($discussion);
        }

        $discussionsData = $this->discussionQueryService->allDiscussionsDataOfForum(
            $forum->tenant()->id(),
            $forum->forumId()->id()
        );

        $this->assertNotNull($discussionsData);
        $this->assertNotEmpty($discussionsData);
        $this->assertCount(count($discussions), $discussionsData);
    }

    public function testDiscussionDataOfId()
    {
        $forum = $this->forumAggregate();
        DomainRegistry::forumRepository()->save($forum);

        $discussion = $this->discussionAggregate($forum);
        DomainRegistry::discussionRepository()->save($discussion);

        $discussionData = $this->discussionQueryService->discussionDataOfId(
            $discussion->tenant()->id(),
            $discussion->discussionId()->id()
        );

        $this->assertNotNull($discussionData);
        $this->assertEquals($discussion->discussionId()->id(), $discussionData->getDiscussionId());
        $this->assertEquals($discussion->forumId()->id(), $discussionData->getForumId());
        $this->assertEquals($discussion->tenant()->id(), $discussionData->getTenantId());
        $this->assertEquals($discussion->author()->emailAddress(), $discussionData->getAuthorEmailAddress());
        $this->assertEquals($discussion->author()->identity(), $discussionData->getAuthorIdentity());
        $this->assertEquals($discussion->author()->name(), $discussionData->getAuthorName());
        $this->assertEquals($discussion->subject(), $discussionData->getSubject());
        $this->assertEquals($discussion->exclusiveOwner(), $discussionData->getExclusiveOwner());
        $this->assertEquals($discussion->isClosed(), $discussionData->isClosed());
    }

    public function testDiscussionIdOfExclusiveOwner()
    {
        $forum = $this->forumAggregate();
        DomainRegistry::forumRepository()->save($forum);

        $discussion = $this->discussionAggregate($forum);
        DomainRegistry::discussionRepository()->save($discussion);

        $discussionId = $this->discussionQueryService->discussionIdOfExclusiveOwner(
            $discussion->tenant()->id(),
            $discussion->exclusiveOwner()
        );

        $this->assertNotNull($discussionId);
        $this->assertEquals($discussion->discussionId()->id(), $discussionId);
    }

    public function testDiscussionPostsDataOfId()
    {
        $forum = $this->forumAggregate();
        DomainRegistry::forumRepository()->save($forum);

        $discussion = $this->discussionAggregate($forum);
        DomainRegistry::discussionRepository()->save($discussion);

        $posts = $this->postAggregates($discussion);

        foreach ($posts as $post) {
            DomainRegistry::postRepository()->save($post);
        }

        $discussionPostsData = $this->discussionQueryService->discussionPostsDataOfId(
            $discussion->tenant()->id(),
            $discussion->discussionId()->id()
        );

        $this->assertNotNull($discussionPostsData);
        $this->assertEquals($discussion->discussionId()->id(), $discussionPostsData->getDiscussionId());
        $this->assertEquals($discussion->forumId()->id(), $discussionPostsData->getForumId());
        $this->assertEquals($discussion->tenant()->id(), $discussionPostsData->getTenantId());
        $this->assertEquals($discussion->author()->emailAddress(), $discussionPostsData->getAuthorEmailAddress());
        $this->assertEquals($discussion->author()->identity(), $discussionPostsData->getAuthorIdentity());
        $this->assertEquals($discussion->author()->name(), $discussionPostsData->getAuthorName());
        $this->assertEquals($discussion->subject(), $discussionPostsData->getSubject());
        $this->assertEquals($discussion->exclusiveOwner(), $discussionPostsData->getExclusiveOwner());
        $this->assertEquals($discussion->isClosed(), $discussionPostsData->isClosed());

        $this->assertNotNull($discussionPostsData->getPosts());
        $this->assertNotEmpty($discussionPostsData->getPosts());
        $this->assertCount(count($posts), $discussionPostsData->getPosts());

        foreach ($discussionPostsData->getPosts() as $post) {
            $this->assertNotNull($post->getAuthorEmailAddress());
            $this->assertNotNull($post->getAuthorName());
            $this->assertNotNull($post->getBodyText());
            $this->assertNotNull($post->getSubject());
            $this->assertContains($post->getAuthorIdentity(), ['jdoe', 'zoe', 'joe']);
        }
    }
}
