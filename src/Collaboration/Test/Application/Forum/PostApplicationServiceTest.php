<?php

namespace SaasOvation\Collaboration\Test\Application\Forum;

use SaasOvation\Collaboration\Test\Application\ApplicationTest;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Domain\Model\Forum\Forum;
use SaasOvation\Collaboration\Domain\Model\Forum\PostId;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

class PostApplicationServiceTest extends ApplicationTest
{
    public function testModeratePost()
    {
        $tenant = new Tenant('01234567');

        $forum = Forum::create(
            $tenant,
            DomainRegistry::forumRepository()->nextIdentity(),
            $this->collaboratorService->creatorFrom($tenant, 'jdoe'),
            $this->collaboratorService->moderatorFrom($tenant, 'jdoe'),
            'A Forum',
            'A forum description.',
            null
        );

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

        $this->postApplicationService->moderatePost(
            $discussion->tenant()->id(),
            $forum->forumId()->id(),
            $result->getPostId(),
            $forum->moderator()->identity(),
            'Post Moderated Subject Test',
            'Post moderated text test...'
        );

        $post = DomainRegistry::postRepository()->postOfId(
            $discussion->tenant(),
            new PostId($result->getPostId())
        );

        $this->assertNotNull($result->getDiscussionId());
        $this->assertNotNull($post);
        $this->assertEquals('Post Moderated Subject Test', $post->subject());
        $this->assertEquals("Post moderated text test...", $post->bodyText());
    }
}
