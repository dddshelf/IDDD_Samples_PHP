<?php

namespace SaasOvation\Collaboration\Test\Application\Forum;

use Rhumsaa\Uuid\Uuid;
use SaasOvation\Collaboration\Test\Application\ApplicationTest;
use SaasOvation\Collaboration\Application\Forum\Data\ForumCommandResult;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionId;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumId;

class ForumApplicationServiceTest extends ApplicationTest
{
    public function testAssignModeratorToForum()
    {
        $forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($forum);

        $this->forumApplicationService->assignModeratorToForum(
            $forum->tenant()->id(),
            $forum->forumId()->id(),
            'newModerator'
        );

        $forum = DomainRegistry::forumRepository()->forumOfId(
            $forum->tenant(),
            $forum->forumId()
        );

        $this->assertNotNull($forum);
        $this->assertEquals('newModerator', $forum->moderator()->identity());
    }

    public function testChangeForumDescription()
    {
        $forum = $this->forumAggregate();

        $this->assertNotEquals('A changed description.', $forum->description());

        DomainRegistry::forumRepository()->save($forum);

        $this->forumApplicationService->changeForumDescription(
            $forum->tenant()->id(),
            $forum->forumId()->id(),
            'A changed description.'
        );

        $forum = DomainRegistry::forumRepository()->forumOfId(
            $forum->tenant(),
            $forum->forumId()
        );

        $this->assertNotNull($forum);
        $this->assertEquals('A changed description.', $forum->description());
    }

    public function testChangeForumSubject()
    {
        $forum = $this->forumAggregate();

        $this->assertNotEquals('A changed subject.', $forum->subject());

        DomainRegistry::forumRepository()->save($forum);

        $this->forumApplicationService->changeForumSubject(
            $forum->tenant()->id(),
            $forum->forumId()->id(),
            'A changed subject.'
        );

        $forum = DomainRegistry::forumRepository()->forumOfId(
            $forum->tenant(),
            $forum->forumId()
        );

        $this->assertNotNull($forum);
        $this->assertEquals('A changed subject.', $forum->subject());
    }

    public function testCloseForum()
    {
        $forum = $this->forumAggregate();

        $this->assertFalse($forum->isClosed());

        DomainRegistry::forumRepository()->save($forum);

        $this->forumApplicationService->closeForum($forum->tenant()->id(), $forum->forumId()->id());

        $forum = DomainRegistry::forumRepository()->forumOfId(
            $forum->tenant(),
            $forum->forumId()
        );

        $this->assertNotNull($forum);
        $this->assertTrue($forum->isClosed());
    }

    public function testReopenForum()
    {
        $forum = $this->forumAggregate();

        $forum->close();

        $this->assertTrue($forum->isClosed());

        DomainRegistry::forumRepository()->save($forum);

        $this->forumApplicationService->reopenForum($forum->tenant()->id(), $forum->forumId()->id());

        $forum = DomainRegistry::forumRepository()->forumOfId(
            $forum->tenant(),
            $forum->forumId()
        );

        $this->assertNotNull($forum);
        $this->assertFalse($forum->isClosed());
    }

    public function testStartForum()
    {
        $forum = $this->forumAggregate();

        $result = new CustomForumCommandResult();

        $this->forumApplicationService->startForum(
            $forum->tenant()->id(),
            $forum->creator()->identity(),
            $forum->moderator()->identity(),
            $forum->subject(),
            $forum->description(),
            $result
        );

        $this->assertNotNull($result->getForumId());

        $newlyStartedForum = DomainRegistry::forumRepository()->forumOfId($forum->tenant(), new ForumId($result->getForumId()));

        $this->assertNotNull($newlyStartedForum);
        $this->assertEquals($forum->tenant(), $newlyStartedForum->tenant());
        $this->assertEquals($result->getForumId(), $newlyStartedForum->forumId()->id());
        $this->assertEquals($forum->creator()->identity(), $newlyStartedForum->creator()->identity());
        $this->assertEquals($forum->moderator()->identity(), $newlyStartedForum->moderator()->identity());
        $this->assertEquals($forum->subject(), $newlyStartedForum->subject());
        $this->assertEquals($forum->description(), $newlyStartedForum->description());
    }

    public function testStartExclusiveForum()
    {
        $forum = $this->forumAggregate();

        $result = new CustomForumCommandResult();

        $exclusiveOwner = strtoupper(Uuid::uuid4());

        $this->forumApplicationService->startExclusiveForum(
            $forum->tenant()->id(),
            $exclusiveOwner,
            $forum->creator()->identity(),
            $forum->moderator()->identity(),
            $forum->subject(),
            $forum->description(),
            $result
        );

        $this->assertNotNull($result->getForumId());

        $newlyStartedForum = DomainRegistry::forumRepository()->forumOfId($forum->tenant(), new ForumId($result->getForumId()));

        $this->assertNotNull($newlyStartedForum);
        $this->assertEquals($forum->tenant(), $newlyStartedForum->tenant());
        $this->assertEquals($result->getForumId(), $newlyStartedForum->forumId()->id());
        $this->assertEquals($forum->creator()->identity(), $newlyStartedForum->creator()->identity());
        $this->assertEquals($forum->moderator()->identity(), $newlyStartedForum->moderator()->identity());
        $this->assertEquals($forum->subject(), $newlyStartedForum->subject());
        $this->assertEquals($forum->description(), $newlyStartedForum->description());
        $this->assertEquals($exclusiveOwner, $newlyStartedForum->exclusiveOwner());
    }

    public function testStartExclusiveForumWithDiscussion()
    {
        $forum = $this->forumAggregate();

        $result = new CustomForumCommandResult();

        $exclusiveOwner = strtoupper(Uuid::uuid4());

        $this->forumApplicationService->startExclusiveForumWithDiscussion(
            $forum->tenant()->id(),
            $exclusiveOwner,
            $forum->creator()->identity(),
            $forum->moderator()->identity(),
            'authorId1',
            $forum->subject(),
            $forum->description(),
            'Discussion Subject',
            $result
        );

        $this->assertNotNull($result->getForumId());
        $this->assertNotNull($result->getDiscussionId());

        $newlyStartedForum = DomainRegistry::forumRepository()->forumOfId($forum->tenant(), new ForumId($result->getForumId()));

        $this->assertNotNull($newlyStartedForum);
        $this->assertEquals($forum->tenant(), $newlyStartedForum->tenant());
        $this->assertEquals($result->getForumId(), $newlyStartedForum->forumId()->id());
        $this->assertEquals($forum->creator()->identity(), $newlyStartedForum->creator()->identity());
        $this->assertEquals($forum->moderator()->identity(), $newlyStartedForum->moderator()->identity());
        $this->assertEquals($forum->subject(), $newlyStartedForum->subject());
        $this->assertEquals($forum->description(), $newlyStartedForum->description());
        $this->assertEquals($exclusiveOwner, $newlyStartedForum->exclusiveOwner());

        $newlyStartedDiscussion = DomainRegistry::discussionRepository()->discussionOfId($forum->tenant(), new DiscussionId($result->getDiscussionId()));

        $this->assertNotNull($newlyStartedDiscussion);
        $this->assertEquals('authorId1', $newlyStartedDiscussion->author()->identity());
        $this->assertEquals('Discussion Subject', $newlyStartedDiscussion->subject());
    }
}
