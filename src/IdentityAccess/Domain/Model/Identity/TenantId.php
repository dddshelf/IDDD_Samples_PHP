<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use Rhumsaa\Uuid\Uuid;
use SaasOvation\Common\Domain\Model\AbstractId;

final class TenantId extends AbstractId
{
    /**
     * @var string
     */
    protected $id;

    protected function validateId($anId)
    {
        return Uuid::isValid($anId);
    }
}
