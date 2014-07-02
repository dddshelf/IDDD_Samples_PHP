<?php

namespace SaasOvation\Collaboration\Application\Forum;

use SaasOvation\Collaboration\Application\Forum\Data\DiscussionData;
use SaasOvation\Collaboration\Application\Forum\Data\ForumData;
use SaasOvation\Collaboration\Application\Forum\Data\ForumDiscussionsData;
use SaasOvation\Common\Port\Adapter\Persistence\AbstractQueryService;

class ForumQueryService extends AbstractQueryService
{
    public function allForumsDataOfTenant($aTenantId)
    {
        $forums = [];

        foreach ($this->database()->forum('tenant_id', $aTenantId) as $aForumRow) {
            $forums[] = $this->buildForumDiscussionDataFrom($aForumRow);
        }

        return $forums;
    }

    public function forumDataOfId($aTenantId, $aForumId)
    {
        return $this->buildForumDataFrom(
            $this->database()->forum('tenant_id = ? and forum_id = ?', $aTenantId, $aForumId)->fetch()
        );
    }

    private function buildForumDataFrom($aForumRow)
    {
        $forumData = new ForumData();

        $forumData->setClosed(boolval($aForumRow['closed']));
        $forumData->setTenantId($aForumRow['tenant_id']);
        $forumData->setSubject($aForumRow['subject']);
        $forumData->setForumId($aForumRow['forum_id']);
        $forumData->setCreatorEmailAddress($aForumRow['creator_email_address']);
        $forumData->setCreatorIdentity($aForumRow['creator_identity']);
        $forumData->setCreatorName($aForumRow['creator_name']);
        $forumData->setDescription($aForumRow['description']);
        $forumData->setExclusiveOwner($aForumRow['exclusive_owner']);
        $forumData->setModeratorEmailAddress($aForumRow['moderator_email_address']);
        $forumData->setModeratorIdentity($aForumRow['moderator_identity']);
        $forumData->setModeratorName($aForumRow['moderator_name']);
        $forumData->setSubject($aForumRow['subject']);
        $forumData->setTenantId($aForumRow['tenant_id']);

        return $forumData;
    }

    public function forumDiscussionsDataOfId($aTenantId, $aForumId)
    {
        return $this->buildForumDiscussionDataFrom(
            $this->database()->forum('tenant_id = ? and forum_id = ?', $aTenantId, $aForumId)->fetch()
        );
    }

    private function buildForumDiscussionDataFrom($aForumRow)
    {
        $forumData = new ForumDiscussionsData();

        $forumData->setClosed(boolval($aForumRow['closed']));
        $forumData->setTenantId($aForumRow['tenant_id']);
        $forumData->setSubject($aForumRow['subject']);
        $forumData->setForumId($aForumRow['forum_id']);
        $forumData->setCreatorEmailAddress($aForumRow['creator_email_address']);
        $forumData->setCreatorIdentity($aForumRow['creator_identity']);
        $forumData->setCreatorName($aForumRow['creator_name']);
        $forumData->setDescription($aForumRow['description']);
        $forumData->setExclusiveOwner($aForumRow['exclusive_owner']);
        $forumData->setModeratorEmailAddress($aForumRow['moderator_email_address']);
        $forumData->setModeratorIdentity($aForumRow['moderator_identity']);
        $forumData->setModeratorName($aForumRow['moderator_name']);
        $forumData->setSubject($aForumRow['subject']);
        $forumData->setTenantId($aForumRow['tenant_id']);

        $discussions = [];

        foreach ($aForumRow->discussion() as $aDiscussionRow) {
            $discussionData = new DiscussionData();
            $discussionData->setTenantId($aDiscussionRow['tenant_id']);
            $discussionData->setSubject($aDiscussionRow['subject']);
            $discussionData->setExclusiveOwner($aDiscussionRow['exclusive_owner']);
            $discussionData->setAuthorEmailAddress($aDiscussionRow['author_email_address']);
            $discussionData->setAuthorIdentity($aDiscussionRow['author_identity']);
            $discussionData->setAuthorName($aDiscussionRow['author_name']);
            $discussionData->setForumId($aDiscussionRow['forum_id']);
            $discussionData->setDiscussionId($aDiscussionRow['discussion_id']);

            $discussions[] = $discussionData;
        }

        $forumData->setDiscussions($discussions);

        return $forumData;
    }

    public function forumIdOfExclusiveOwner($aTenantId, $anExclusiveOwner)
    {
        return $this->queryString(
            'select forum_id from tbl_vw_forum where tenant_id = ? and exclusive_owner = ?',
            $aTenantId,
            $anExclusiveOwner
        );
    }
}
