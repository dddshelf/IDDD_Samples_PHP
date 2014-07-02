<?php

namespace SaasOvation\Collaboration\Test\Application\Forum;

use SaasOvation\Collaboration\Test\Application\ApplicationTest;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;

class PostQueryServiceTest extends ApplicationTest
{
    public function testAllPostsDataOfDiscussion()
    {
        $forum = $this->forumAggregate();
        DomainRegistry::forumRepository()->save($forum);

        $discussion = $this->discussionAggregate($forum);
        DomainRegistry::discussionRepository()->save($discussion);

        $posts = $this->postAggregates($discussion);

        foreach ($posts as $post) {
            DomainRegistry::postRepository()->save($post);
        }

        $postsData = $this->postQueryService->allPostsDataOfDiscussion(
            $forum->tenant()->id(),
            $discussion->discussionId()->id()
        );

        $this->assertNotNull($postsData);
        $this->assertNotEmpty($postsData);
        $this->assertCount(count($posts), $postsData);
    }

    public function testPostDataOfId()
    {
        $forum = $this->forumAggregate();
        DomainRegistry::forumRepository()->save($forum);

        $discussion = $this->discussionAggregate($forum);
        DomainRegistry::discussionRepository()->save($discussion);

        $post = $this->postAggregate($discussion);
        DomainRegistry::postRepository()->save($post);

        $postData = $this->postQueryService->postDataOfId($post->tenant()->id(), $post->postId()->id());

        $this->assertNotNull($postData);
        $this->assertEquals($post->postId()->id(), $postData->getPostId());
        $this->assertEquals($post->discussionId()->id(), $postData->getDiscussionId());
        $this->assertEquals($post->forumId()->id(), $postData->getForumId());
        $this->assertEquals($post->tenant()->id(), $postData->getTenantId());
        $this->assertEquals($post->author()->emailAddress(), $postData->getAuthorEmailAddress());
        $this->assertEquals($post->author()->identity(), $postData->getAuthorIdentity());
        $this->assertEquals($post->author()->name(), $postData->getAuthorName());
        $this->assertEquals($post->subject(), $postData->getSubject());
        $this->assertEquals($post->bodyText(), $postData->getBodyText());

        if (null === $postData->getReplyToPostId()) {
            $this->assertEquals($post->replyToPostId(), $postData->getReplyToPostId());
        } else {
            $this->assertEquals($post->replyToPostId()->id(), $postData->getReplyToPostId());
        }
    }
}
