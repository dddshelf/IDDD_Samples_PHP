<?php

namespace SaasOvation\Collaboration\Test\Application\Forum;

use SaasOvation\Collaboration\Test\Application\ApplicationTest;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;

class ForumQueryServiceTest extends ApplicationTest
{
    public function testAllForumsDataOfTenant()
    {
        $forums = $this->forumAggregates();

        foreach ($forums as $forum) {
            DomainRegistry::forumRepository()->save($forum);
        }

        $forumsData = $this->forumQueryService->allForumsDataOfTenant($forums[0]->tenant()->id());

        $this->assertNotNull($forumsData);
        $this->assertNotEmpty($forumsData);
        $this->assertCount(count($forums), $forumsData);
    }

    public function testForumDataOfId()
    {
        $forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($forum);

        $forumData = $this->forumQueryService->forumDataOfId($forum->tenant()->id(), $forum->forumId()->id());

        $this->assertNotNull($forumData);
        $this->assertEquals($forum->forumId()->id(), $forumData->getForumId());
        $this->assertEquals($forum->tenant()->id(), $forumData->getTenantId());
        $this->assertEquals($forum->creator()->emailAddress(), $forumData->getCreatorEmailAddress());
        $this->assertEquals($forum->creator()->identity(), $forumData->getCreatorIdentity());
        $this->assertEquals($forum->creator()->name(), $forumData->getCreatorName());
        $this->assertEquals($forum->description(), $forumData->getDescription());
        $this->assertEquals($forum->exclusiveOwner(), $forumData->getExclusiveOwner());
        $this->assertEquals($forum->isClosed(), $forumData->isClosed());
        $this->assertEquals($forum->subject(), $forumData->getSubject());
        $this->assertEquals($forum->moderator()->emailAddress(), $forumData->getModeratorEmailAddress());
        $this->assertEquals($forum->moderator()->identity(), $forumData->getModeratorIdentity());
        $this->assertEquals($forum->moderator()->name(), $forumData->getModeratorName());
    }

    public function testForumDiscussionsDataOfId()
    {
        $forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($forum);

        $discussions = $this->discussionAggregates($forum);

        foreach ($discussions as $discussion) {
            DomainRegistry::discussionRepository()->save($discussion);
        }

        $forumDiscussionsData = $this->forumQueryService->forumDiscussionsDataOfId($forum->tenant()->id(), $forum->forumId()->id());

        $this->assertNotNull($forumDiscussionsData);
        $this->assertEquals($forum->forumId()->id(), $forumDiscussionsData->getForumId());
        $this->assertEquals($forum->tenant()->id(), $forumDiscussionsData->getTenantId());
        $this->assertEquals($forum->creator()->emailAddress(), $forumDiscussionsData->getCreatorEmailAddress());
        $this->assertEquals($forum->creator()->identity(), $forumDiscussionsData->getCreatorIdentity());
        $this->assertEquals($forum->creator()->name(), $forumDiscussionsData->getCreatorName());
        $this->assertEquals($forum->description(), $forumDiscussionsData->getDescription());
        $this->assertEquals($forum->exclusiveOwner(), $forumDiscussionsData->getExclusiveOwner());
        $this->assertEquals($forum->isClosed(), $forumDiscussionsData->isClosed());
        $this->assertEquals($forum->subject(), $forumDiscussionsData->getSubject());
        $this->assertEquals($forum->moderator()->emailAddress(), $forumDiscussionsData->getModeratorEmailAddress());
        $this->assertEquals($forum->moderator()->identity(), $forumDiscussionsData->getModeratorIdentity());
        $this->assertEquals($forum->moderator()->name(), $forumDiscussionsData->getModeratorName());

        $this->assertNotNull($forumDiscussionsData->getDiscussions());
        $this->assertNotEmpty($forumDiscussionsData->getDiscussions());
        $this->assertCount(3, $forumDiscussionsData->getDiscussions());
    }

    public function testForumIdOfExclusiveOwner()
    {
        $forum = $this->forumAggregate();

        DomainRegistry::forumRepository()->save($forum);

        $forumId = $this->forumQueryService->forumIdOfExclusiveOwner($forum->tenant()->id(), $forum->exclusiveOwner());

        $this->assertNotNull($forumId);
        $this->assertEquals($forum->forumId()->id(), $forumId);
    }
}
