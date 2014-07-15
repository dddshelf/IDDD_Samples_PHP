<?php

namespace SaasOvation\Collaboration\Application\Forum;

use SaasOvation\Collaboration\Application\Forum\Data\ForumCommandResult;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Collaborator\CollaboratorService;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Creator;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Moderator;
use SaasOvation\Collaboration\Domain\Model\Forum\Discussion;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionId;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionRepository;
use SaasOvation\Collaboration\Domain\Model\Forum\Forum;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumId;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumIdentityService;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumRepository;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

class ForumApplicationService
{
    /**
     * @var CollaboratorService
     */
    private $collaboratorService;

    /**
     * @var DiscussionQueryService
     */
    private $discussionQueryService;

    /**
     * @var DiscussionRepository
     */
    private $discussionRepository;

    /**
     * @var ForumIdentityService
     */
    private $forumIdentityService;

    /**
     * @var ForumQueryService
     */
    private $forumQueryService;

    /**
     * @var ForumRepository
     */
    private $forumRepository;

    public function __construct(
        ForumQueryService $aForumQueryService,
        ForumRepository $aForumRepository,
        ForumIdentityService $aForumIdentityService,
        DiscussionQueryService $aDiscussionQueryService,
        DiscussionRepository $aDiscussionRepository,
        CollaboratorService $aCollaboratorService
    ) {
        $this->collaboratorService = $aCollaboratorService;
        $this->discussionQueryService = $aDiscussionQueryService;
        $this->discussionRepository = $aDiscussionRepository;
        $this->forumIdentityService = $aForumIdentityService;
        $this->forumQueryService = $aForumQueryService;
        $this->forumRepository = $aForumRepository;
    }

    public function assignModeratorToForum(
        $aTenantId,
        $aForumId,
        $aModeratorId
    ) {
        $tenant = new Tenant($aTenantId);

        $forum = $this->forumRepository()->forumOfId(
            $tenant,
            new ForumId($aForumId)
        );

        $moderator = $this->collaboratorService()->moderatorFrom($tenant, $aModeratorId);

        $forum->assignModerator($moderator);

        $this->forumRepository()->save($forum);
    }

    public function changeForumDescription(
        $aTenantId,
        $aForumId,
        $aDescription
    ) {

        $tenant = new Tenant($aTenantId);

        $forum =
            $this
                ->forumRepository()
                ->forumOfId(
                    $tenant,
                    new ForumId($aForumId)
                )
        ;

        $forum->changeDescription($aDescription);

        $this->forumRepository()->save($forum);
    }

    public function changeForumSubject($aTenantId, $aForumId, $aSubject)
    {
        $tenant = new Tenant($aTenantId);

        $forum =
            $this
                ->forumRepository()
                ->forumOfId(
                    $tenant,
                    new ForumId($aForumId)
                )
        ;

        $forum->changeSubject($aSubject);

        $this->forumRepository()->save($forum);
    }

    public function closeForum($aTenantId, $aForumId)
    {
        $tenant = new Tenant($aTenantId);

        $forum =
            $this
                ->forumRepository()
                ->forumOfId(
                    $tenant,
                    new ForumId($aForumId)
                )
        ;

        $forum->close();

        $this->forumRepository()->save($forum);
    }

    public function reopenForum($aTenantId, $aForumId)
    {
        $tenant = new Tenant($aTenantId);

        $forum =
            $this->forumRepository()
            ->forumOfId(
                $tenant,
                new ForumId($aForumId)
            )
        ;

        $forum->reopen();

        $this->forumRepository()->save($forum);
    }

    public function startForum(
        $aTenantId,
        $aCreatorId,
        $aModeratorId,
        $aSubject,
        $aDescription,
        ForumCommandResult $aResult
    ) {

        $forum = $this->startNewForum(
            new Tenant($aTenantId),
            $aCreatorId,
            $aModeratorId,
            $aSubject,
            $aDescription,
            null
        );

        if (null !== $aResult) {
            $aResult->resultingForumId($forum->forumId()->id());
        }
    }

    public function startExclusiveForum(
        $aTenantId,
        $anExclusiveOwner,
        $aCreatorId,
        $aModeratorId,
        $aSubject,
        $aDescription,
        ForumCommandResult $aResult
    ) {

        $tenant = new Tenant($aTenantId);

        $forumId = $this->forumQueryService()->forumIdOfExclusiveOwner(
            $aTenantId,
            $anExclusiveOwner
        );

        $forum = null;

        if ($forumId) {
            $forum = $this->forumRepository()->forumOfId(
                $tenant,
                new ForumId($forumId)
            );
        }

        if (null === $forum) {
            $forum = $this->startNewForum(
                $tenant,
                $aCreatorId,
                $aModeratorId,
                $aSubject,
                $aDescription,
                $anExclusiveOwner
            );
        }

        if (null !== $aResult) {
            $aResult->resultingForumId($forum->forumId()->id());
        }
    }

    public function startExclusiveForumWithDiscussion(
        $aTenantId,
        $anExclusiveOwner,
        $aCreatorId,
        $aModeratorId,
        $anAuthorId,
        $aForumSubject,
        $aForumDescription,
        $aDiscussionSubject,
        ForumCommandResult $aResult
    ) {
        $tenant = new Tenant($aTenantId);

        $forumId = $this->forumQueryService()->forumIdOfExclusiveOwner(
            $aTenantId,
            $anExclusiveOwner
        );

        $forum = null;

        if ($forumId) {
            $forum = $this->forumRepository()->forumOfId($tenant, new ForumId($forumId));
        }

        if (null === $forum) {
            $forum = $this->startNewForum(
                $tenant,
                $aCreatorId,
                $aModeratorId,
                $aForumSubject,
                $aForumDescription,
                $anExclusiveOwner
            );
        }

        $discussionId = $this->discussionQueryService()->discussionIdOfExclusiveOwner($aTenantId, $anExclusiveOwner);

        $discussion = null;

        if ($discussionId) {
            $discussion = $this->discussionRepository()->discussionOfId($tenant, new DiscussionId($discussionId));
        }

        if (null === $discussion) {
            $author = $this->collaboratorService()->authorFrom($tenant, $anAuthorId);

            $discussion = $forum->startDiscussionFor(
                $this->forumIdentityService(),
                $author,
                $aDiscussionSubject,
                $anExclusiveOwner
            );

            $this->discussionRepository()->save($discussion);
        }

        if (null !== $aResult) {
            $aResult->resultingForumId($forum->forumId()->id());
            $aResult->resultingDiscussionId($discussion->discussionId()->id());
        }
    }

    private function collaboratorService()
    {
        return $this->collaboratorService;
    }

    private function discussionQueryService()
    {
        return $this->discussionQueryService;
    }

    private function discussionRepository()
    {
        return $this->discussionRepository;
    }

    private function forumIdentityService()
    {
        return $this->forumIdentityService;
    }

    private function forumQueryService()
    {
        return $this->forumQueryService;
    }

    private function forumRepository()
    {
        return $this->forumRepository;
    }

    private function startNewForum(
        Tenant $aTenant,
        $aCreatorId,
        $aModeratorId,
        $aSubject,
        $aDescription,
        $anExclusiveOwner
    ) {

        $creator = $this->collaboratorService()->creatorFrom($aTenant, $aCreatorId);
        $moderator = $this->collaboratorService()->moderatorFrom($aTenant, $aModeratorId);

        $newForum = Forum::create(
            $aTenant,
            $this->forumRepository()->nextIdentity(),
            $creator,
            $moderator,
            $aSubject,
            $aDescription,
            $anExclusiveOwner
        );

        $this->forumRepository()->save($newForum);

        return $newForum;
    }
}
