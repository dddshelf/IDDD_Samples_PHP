<?php

namespace SaasOvation\Collaboration\Application\Forum;

use SaasOvation\Collaboration\Application\Forum\Data\DiscussionData;
use SaasOvation\Collaboration\Application\Forum\Data\DiscussionPostsData;
use SaasOvation\Collaboration\Application\Forum\Data\PostData;
use SaasOvation\Common\Port\Adapter\Persistence\AbstractQueryService;

class DiscussionQueryService extends AbstractQueryService
{
    public function allDiscussionsDataOfForum($aTenantId, $aForumId)
    {
        $discusionRows = $this->database()->discussion('tenant_id = ? and forum_id = ?', $aTenantId, $aForumId);

        $discussionDatas = [];

        foreach ($discusionRows as $aDiscussionRow) {
            $discussionDatas[] = $this->buildDiscussionDataFrom($aDiscussionRow);
        }

        return $discussionDatas;
    }

    public function discussionDataOfId($aTenantId, $aDiscussionId)
    {
        return $this->buildDiscussionDataFrom(
            $this->database()->discussion('tenant_id = ? and discussion_id = ?', $aTenantId, $aDiscussionId)->fetch()
        );
    }

    private function buildDiscussionDataFrom($aDiscussionRow)
    {
        $discussionData = new DiscussionData();

        $discussionData->setClosed(boolval($aDiscussionRow['closed']));
        $discussionData->setTenantId($aDiscussionRow['tenant_id']);
        $discussionData->setAuthorEmailAddress($aDiscussionRow['author_email_address']);
        $discussionData->setAuthorIdentity($aDiscussionRow['author_identity']);
        $discussionData->setAuthorName($aDiscussionRow['author_name']);
        $discussionData->setDiscussionId($aDiscussionRow['discussion_id']);
        $discussionData->setExclusiveOwner($aDiscussionRow['exclusive_owner']);
        $discussionData->setForumId($aDiscussionRow['forum_id']);
        $discussionData->setSubject($aDiscussionRow['subject']);

        return $discussionData;
    }

    public function discussionIdOfExclusiveOwner($aTenantId, $anExclusiveOwner)
    {
        return $this->queryString(
            'select discussion_id from tbl_vw_discussion where tenant_id = ? and exclusive_owner = ?',
            $aTenantId,
            $anExclusiveOwner
        );
    }

    public function discussionPostsDataOfId($aTenantId, $aDiscussionId)
    {
        return $this->buildDiscussionDataWithPostsFrom(
            $this->database()->discussion('tenant_id = ? and discussion_id = ?', $aTenantId, $aDiscussionId)->fetch()
        );
    }

    private function buildDiscussionDataWithPostsFrom($aDiscussionRow)
    {
        $aDiscussionPostsData = new DiscussionPostsData();

        $aDiscussionPostsData->setClosed(boolval($aDiscussionRow['closed']));
        $aDiscussionPostsData->setTenantId($aDiscussionRow['tenant_id']);
        $aDiscussionPostsData->setAuthorEmailAddress($aDiscussionRow['author_email_address']);
        $aDiscussionPostsData->setAuthorIdentity($aDiscussionRow['author_identity']);
        $aDiscussionPostsData->setAuthorName($aDiscussionRow['author_name']);
        $aDiscussionPostsData->setDiscussionId($aDiscussionRow['discussion_id']);
        $aDiscussionPostsData->setExclusiveOwner($aDiscussionRow['exclusive_owner']);
        $aDiscussionPostsData->setForumId($aDiscussionRow['forum_id']);
        $aDiscussionPostsData->setSubject($aDiscussionRow['subject']);

        $posts = [];

        foreach ($aDiscussionRow->post() as $aPostRow) {
            $aPostData = new PostData();
            $aPostData->setAuthorName($aPostRow['author_name']);
            $aPostData->setAuthorIdentity($aPostRow['author_identity']);
            $aPostData->setAuthorEmailAddress($aPostRow['author_email_address']);
            $aPostData->setAuthorName($aPostRow['author_name']);
            $aPostData->setBodyText($aPostRow['body_text']);
            $aPostData->setChangedOn($aPostRow['changed_on']);
            $aPostData->setCreatedOn($aPostRow['created_on']);
            $aPostData->setDiscussionId($aPostRow['discussion_id']);
            $aPostData->setForumId($aPostRow['forum_id']);
            $aPostData->setPostId($aPostRow['post_id']);
            $aPostData->setReplyToPostId($aPostRow['reply_to_post_id']);
            $aPostData->setTenantId($aPostRow['tenant_id']);
            $aPostData->setSubject($aPostRow['subject']);

            $posts[] = $aPostData;
        }

        $aDiscussionPostsData->setPosts($posts);

        return $aDiscussionPostsData;
    }
}
