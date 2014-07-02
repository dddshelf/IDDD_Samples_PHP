<?php

namespace SaasOvation\Collaboration\Application\Forum;

use SaasOvation\Collaboration\Application\Forum\Data\PostData;
use SaasOvation\Common\Port\Adapter\Persistence\AbstractQueryService;

class PostQueryService extends AbstractQueryService
{
    public function allPostsDataOfDiscussion($aTenantId, $aDiscussionId)
    {
        $posts = [];

        foreach ($this->database()->post('tenant_id = ? and discussion_id = ?', $aTenantId, $aDiscussionId) as $aPostRow) {
            $posts[] = $this->buildPostDataFrom($aPostRow);
        }

        return $posts;
    }

    public function postDataOfId($aTenantId, $aPostId)
    {
        return $this->buildPostDataFrom(
            $this->database()->post('tenant_id = ? and post_id = ?', $aTenantId, $aPostId)->fetch()
        );
    }

    private function buildPostDataFrom($aPostRow)
    {
        $aPostData = new PostData();

        $aPostData->setDiscussionId($aPostRow['discussion_id']);
        $aPostData->setAuthorName($aPostRow['author_name']);
        $aPostData->setAuthorIdentity($aPostRow['author_identity']);
        $aPostData->setAuthorEmailAddress($aPostRow['author_email_address']);
        $aPostData->setBodyText($aPostRow['body_text']);
        $aPostData->setChangedOn($aPostRow['changed_on']);
        $aPostData->setCreatedOn($aPostRow['created_on']);
        $aPostData->setForumId($aPostRow['forum_id']);
        $aPostData->setPostId($aPostRow['post_id']);
        $aPostData->setReplyToPostId($aPostRow['reply_to_post_id']);
        $aPostData->setSubject($aPostRow['subject']);
        $aPostData->setTenantId($aPostRow['tenant_id']);

        return $aPostData;
    }
}