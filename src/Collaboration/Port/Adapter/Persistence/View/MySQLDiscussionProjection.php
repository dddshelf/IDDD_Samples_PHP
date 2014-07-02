<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\View;

use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionClosed;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionReopened;
use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionStarted;
use SaasOvation\Common\Event\Sourcing\EventDispatcher;
use SaasOvation\Common\Port\Adapter\Persistence\AbstractProjection;

class MySQLDiscussionProjection extends AbstractProjection implements EventDispatcher
{
    protected function whenDiscussionClosed(DiscussionClosed $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_discussion SET closed = 1 WHERE tenant_id = ? AND discussion_id = ?'
        );
        
        $statement->bindValue(1, $anEvent->tenant()->id());
        $statement->bindValue(2, $anEvent->discussionId()->id());
        
        $this->execute($statement);
    }

    protected function whenDiscussionReopened(DiscussionReopened $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_discussion SET closed = 0 WHERE tenant_id = ? AND discussion_id = ?'
        );

        $statement->bindValue(1, $anEvent->tenant()->id());
        $statement->bindValue(2, $anEvent->discussionId()->id());

        $this->execute($statement);
    }

    protected function whenDiscussionStarted(DiscussionStarted $anEvent)
    {
        // idempotent operation
        if (
            $this->exists(
                'SELECT discussion_id FROM tbl_vw_discussion WHERE tenant_id = ? AND discussion_id = ?',
                $anEvent->tenant()->id(),
                $anEvent->discussionId()->id())
        ) {
            return;
        }

        $statement = $this->connection()->prepare(
            'INSERT INTO tbl_vw_discussion (
                 discussion_id,
                 author_email_address,
                 author_identity,
                 author_name,
                 closed,
                 exclusive_owner,
                 forum_id,
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
                 ?
             )'
        );

        $statement->bindValue(1, $anEvent->discussionId()->id());
        $statement->bindValue(2, $anEvent->author()->emailAddress());
        $statement->bindValue(3, $anEvent->author()->identity());
        $statement->bindValue(4, $anEvent->author()->name());
        $statement->bindValue(5, 0);
        $statement->bindValue(6, $anEvent->exclusiveOwner());
        $statement->bindValue(7, $anEvent->forumId()->id());
        $statement->bindValue(8, $anEvent->subject());
        $statement->bindValue(9, $anEvent->tenant()->id());

        $this->execute($statement);
    }

    protected function understoodEventTypes()
    {
        return [
            'SaasOvation\Collaboration\Domain\Model\Forum\DiscussionClosed',
            'SaasOvation\Collaboration\Domain\Model\Forum\DiscussionReopened',
            'SaasOvation\Collaboration\Domain\Model\Forum\DiscussionStarted',
        ];
    }
}
