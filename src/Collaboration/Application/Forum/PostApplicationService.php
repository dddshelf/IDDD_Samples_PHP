<?php

namespace SaasOvation\Collaboration\Application\Forum;

use SaasOvation\Collaboration\Domain\Model\Collaborator\CollaboratorService;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Moderator;
use SaasOvation\Collaboration\Domain\Model\Forum\Forum;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumId;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumRepository;
use SaasOvation\Collaboration\Domain\Model\Forum\Post;
use SaasOvation\Collaboration\Domain\Model\Forum\PostId;
use SaasOvation\Collaboration\Domain\Model\Forum\PostRepository;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

class PostApplicationService
{
    /**
     * @var CollaboratorService
     */
    private $collaboratorService;

    /**
     * @var ForumRepository
     */
    private $forumRepository;

    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct(
        PostRepository $aPostRepository,
        ForumRepository $aForumRepository,
        CollaboratorService $aCollaboratorService
    ) {
        $this->collaboratorService = $aCollaboratorService;
        $this->forumRepository = $aForumRepository;
        $this->postRepository = $aPostRepository;
    }

    public function moderatePost(
        $aTenantId,
        $aForumId,
        $aPostId,
        $aModeratorId,
        $aSubject,
        $aBodyText
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

        $moderator = $this->collaboratorService()->moderatorFrom($tenant, $aModeratorId);
        $post = $this->postRepository()->postOfId($tenant, new PostId($aPostId));
        $forum->moderatePost($post, $moderator, $aSubject, $aBodyText);

        $this->postRepository()->save($post);
    }

    private function collaboratorService()
    {
        return $this->collaboratorService;
    }

    private function forumRepository()
    {
        return $this->forumRepository;
    }

    private function postRepository()
    {
        return $this->postRepository;
    }
}
