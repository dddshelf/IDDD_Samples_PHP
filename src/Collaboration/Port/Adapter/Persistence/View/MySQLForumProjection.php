<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\View;

use SaasOvation\Collaboration\Domain\Model\Forum\ForumClosed;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumDescriptionChanged;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumModeratorChanged;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumReopened;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumStarted;
use SaasOvation\Collaboration\Domain\Model\Forum\ForumSubjectChanged;
use SaasOvation\Common\Event\Sourcing\EventDispatcher;
use SaasOvation\Common\Port\Adapter\Persistence\AbstractProjection;

class MySQLForumProjection extends AbstractProjection implements EventDispatcher
{
    protected function whenForumClosed(ForumClosed $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_forum SET closed = 1 WHERE tenant_id = ? AND forum_id = ?'
        );
        
        $statement->bindValue(1, $anEvent->tenant()->id());
        $statement->bindValue(2, $anEvent->forumId()->id());
        
        $this->execute($statement);
    }
    
    protected function whenForumDescriptionChanged(ForumDescriptionChanged $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_forum SET description = ? WHERE tenant_id = ? AND forum_id = ?'
        );

        $statement->bindValue(1, $anEvent->description());
        $statement->bindValue(2, $anEvent->tenant()->id());
        $statement->bindValue(3, $anEvent->forumId()->id());

        $this->execute($statement);
    }
    
    protected function whenForumModeratorChanged(ForumModeratorChanged $anEvent)
    {
        $statement = $this->connection()->prepare(
            "UPDATE tbl_vw_forum SET moderator_email_address = ?, moderator_identity = ?, moderator_name = ? WHERE tenant_id = ? AND forum_id = ?"
        );

        $statement->bindValue(1, $anEvent->moderator()->emailAddress());
        $statement->bindValue(2, $anEvent->moderator()->identity());
        $statement->bindValue(3, $anEvent->moderator()->name());
        $statement->bindValue(4, $anEvent->tenant()->id());
        $statement->bindValue(5, $anEvent->forumId()->id());

        $this->execute($statement);
    }
    
    protected function whenForumReopened(ForumReopened $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_forum SET closed = 0 WHERE tenant_id = ? AND forum_id = ?'
        );

        $statement->bindValue(1, $anEvent->tenant()->id());
        $statement->bindValue(2, $anEvent->forumId()->id());

        $this->execute($statement);
    }
    
    protected function whenForumStarted(ForumStarted $anEvent)
    {
        // idempotent operation
        if (
            $this->exists(
                'SELECT forum_id FROM tbl_vw_forum WHERE tenant_id = ? AND forum_id = ?',
                $anEvent->tenant()->id(),
                $anEvent->forumId()->id())
        ) {
            return;
        }

        $statement = $this->connection()->prepare(
            'INSERT INTO tbl_vw_forum (
                 forum_id,
                 closed,
                 creator_email_address,
                 creator_identity,
                 creator_name,
                 description,
                 exclusive_owner,
                 moderator_email_address,
                 moderator_identity,
                 moderator_name,
                 subject,
                 tenant_id
             ) values (
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?
             )'
        );

        $statement->bindValue(1, $anEvent->forumId()->id());
        $statement->bindValue(2, 0);
        $statement->bindValue(3, $anEvent->creator()->emailAddress());
        $statement->bindValue(4, $anEvent->creator()->identity());
        $statement->bindValue(5, $anEvent->creator()->name());
        $statement->bindValue(6, $anEvent->description());
        $statement->bindValue(7, $anEvent->exclusiveOwner());
        $statement->bindValue(8, $anEvent->moderator()->emailAddress());
        $statement->bindValue(9, $anEvent->moderator()->identity());
        $statement->bindValue(10, $anEvent->moderator()->name());
        $statement->bindValue(11, $anEvent->subject());
        $statement->bindValue(12, $anEvent->tenant()->id());

        $this->execute($statement);
    }
    
    protected function whenForumSubjectChanged(ForumSubjectChanged $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_forum SET subject = ? WHERE tenant_id = ? AND forum_id = ?'
        );

        $statement->bindValue(1, $anEvent->subject());
        $statement->bindValue(2, $anEvent->tenant()->id());
        $statement->bindValue(3, $anEvent->forumId()->id());

        $this->execute($statement);
    }

    protected function understoodEventTypes()
    {
        return [
            'SaasOvation\Collaboration\Domain\Model\Forum\ForumClosed',
            'SaasOvation\Collaboration\Domain\Model\Forum\ForumDescriptionChanged',
            'SaasOvation\Collaboration\Domain\Model\Forum\ForumModeratorChanged',
            'SaasOvation\Collaboration\Domain\Model\Forum\ForumReopened',
            'SaasOvation\Collaboration\Domain\Model\Forum\ForumStarted',
            'SaasOvation\Collaboration\Domain\Model\Forum\ForumSubjectChanged'
        ];
    }
}
