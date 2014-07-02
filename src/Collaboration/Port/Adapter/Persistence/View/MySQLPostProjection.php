<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\View;

use SaasOvation\Collaboration\Domain\Model\Forum\PostContentAltered;
use SaasOvation\Collaboration\Domain\Model\Forum\PostedToDiscussion;
use SaasOvation\Common\Event\Sourcing\EventDispatcher;
use SaasOvation\Common\Port\Adapter\Persistence\AbstractProjection;

class MySQLPostProjection extends AbstractProjection implements EventDispatcher
{
    protected function whenPostContentAltered(PostContentAltered $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_post SET body_text = ?, subject = ?, changed_on = ? WHERE tenant_id = ? AND forum_id = ?'
        );

        $statement->bindValue(1, $anEvent->bodyText());
        $statement->bindValue(2, $anEvent->subject());
        $statement->bindValue(3, $anEvent->occurredOn()->getTimestamp());
        $statement->bindValue(4, $anEvent->tenant()->id());
        $statement->bindValue(5, $anEvent->postId()->id());

        $this->execute($statement);
    }
    
    protected function whenPostedToDiscussion(PostedToDiscussion $anEvent)
    {
        // idempotent operation
        if (
            $this->exists(
                'SELECT post_id FROM tbl_vw_post WHERE tenant_id = ? AND post_id = ?',
                $anEvent->tenant()->id(),
                $anEvent->postId()->id())
        ) {
            return;
        }

        $statement = $this->connection()->prepare(
            'INSERT INTO tbl_vw_post (
                 post_id,
                 author_email_address,
                 author_identity,
                 author_name,
                 body_text,
                 changed_on,
                 created_on,
                 discussion_id,
                 forum_id,
                 reply_to_post_id,
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

        $statement->bindValue(1, $anEvent->postId()->id());
        $statement->bindValue(2, $anEvent->author()->emailAddress());
        $statement->bindValue(3, $anEvent->author()->identity());
        $statement->bindValue(4, $anEvent->author()->name());
        $statement->bindValue(5, $anEvent->bodyText());
        $statement->bindValue(6, $anEvent->occurredOn()->getTimestamp());
        $statement->bindValue(7, $anEvent->occurredOn()->getTimestamp());
        $statement->bindValue(8, $anEvent->discussionId()->id());
        $statement->bindValue(9, $anEvent->forumId()->id());
        $statement->bindValue(10, null === $anEvent->replyToPost() ? null : $anEvent->replyToPost()->id());
        $statement->bindValue(11, $anEvent->subject());
        $statement->bindValue(12, $anEvent->tenant()->id());

        $this->execute($statement);
    }

    protected function understoodEventTypes()
    {
        return [
            'SaasOvation\Collaboration\Domain\Model\Forum\PostContentAltered',
            'SaasOvation\Collaboration\Domain\Model\Forum\PostedToDiscussion'
        ];
    }
}
