<?php

namespace SaasOvation\Collaboration\Application\Forum;

use SaasOvation\Collaboration\Application\Forum\Data\DiscussionCommandResult;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Collaborator\CollaboratorService;
use SaasOvation\Collaboration\Domain\Model\Forum\Discussion;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionId;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionRepository;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumIdentityService;
use SaasOvation\Collaboration\Domain\Model\Forum\Post;
use SaasOvation\Collaboration\Domain\Model\Forum\PostId;
use SaasOvation\Collaboration\Domain\Model\Forum\PostRepository;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

class DiscussionApplicationService
{
    /**
     * @var CollaboratorService
     */
    private $collaboratorService;
    
    /**
     * @var DiscussionRepository
     */
    private $discussionRepository;

    /**
     * @var DiscussionRepository
     */
    private $forumIdentityService;

    /**
     * @var PostRepository
     */
    private $postRepository;
    
    public function __construct(
        DiscussionRepository $aDiscussionRepository,
        ForumIdentityService $aForumIdentityService,
        PostRepository $aPostRepository,
        CollaboratorService $aCollaboratorService
    ) {
        $this->collaboratorService = $aCollaboratorService;
        $this->discussionRepository = $aDiscussionRepository;
        $this->forumIdentityService = $aForumIdentityService;
        $this->postRepository = $aPostRepository;
    }

    public function closeDiscussion($aTenantId, $aDiscussionId)
    {
        $discussion = $this->discussionRepository()->discussionOfId(
            new Tenant($aTenantId),
            new DiscussionId($aDiscussionId)
        );

        $discussion->close();

        $this->discussionRepository()->save($discussion);
    }

    public function postToDiscussion(
        $aTenantId,
        $aDiscussionId,
        $anAuthorId,
        $aSubject,
        $aBodyText,
        DiscussionCommandResult $aDiscussionCommandResult
    ) {

        $discussion = $this->discussionRepository()->discussionOfId(
            new Tenant($aTenantId),
            new DiscussionId($aDiscussionId)
        );

        $author = $this->collaboratorService()->authorFrom(new Tenant($aTenantId), $anAuthorId);

        $post = $discussion->postWithoutReply($this->forumIdentityService(), $author, $aSubject, $aBodyText);

        $this->postRepository()->save($post);

        $aDiscussionCommandResult->resultingDiscussionId($aDiscussionId);
        $aDiscussionCommandResult->resultingPostId($post->postId()->id());
    }

    public function postToDiscussionInReplyTo(
        $aTenantId,
        $aDiscussionId,
        $aReplyToPostId,
        $anAuthorId,
        $aSubject,
        $aBodyText,
        DiscussionCommandResult $aDiscussionCommandResult
    ) {

        $discussion = $this->discussionRepository()->discussionOfId(
            new Tenant($aTenantId),
            new DiscussionId($aDiscussionId)
        );

        $author = $this->collaboratorService()->authorFrom(new Tenant($aTenantId), $anAuthorId);

        $post = $discussion->postWithReply(
            $this->forumIdentityService(),
            new PostId($aReplyToPostId),
            $author,
            $aSubject,
            $aBodyText
        );

        $this->postRepository()->save($post);

        $aDiscussionCommandResult->resultingDiscussionId($aDiscussionId);
        $aDiscussionCommandResult->resultingPostId($post->postId()->id());
        $aDiscussionCommandResult->resultingInReplyToPostId($aReplyToPostId);
    }

    public function reopenDiscussion($aTenantId, $aDiscussionId)
    {
        $discussion =
            $this->discussionRepository()
            ->discussionOfId(
                new Tenant($aTenantId),
                new DiscussionId($aDiscussionId)
            )
        ;

        $discussion->reopen();

        $this->discussionRepository()->save($discussion);
    }

    private function collaboratorService()
    {
        return $this->collaboratorService;
    }

    private function discussionRepository()
    {
        return $this->discussionRepository;
    }

    private function forumIdentityService()
    {
        return $this->forumIdentityService;
    }

    private function postRepository()
    {
        return $this->postRepository;
    }
}
