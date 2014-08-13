<?php

namespace SaasOvation\IdentityAccess\Infrastructure\Services;

use SaasOvation\Common\AssertionConcern;
use SaasOvation\IdentityAccess\Domain\Model\Identity\EncryptionService;

class MD5EncryptionService extends AssertionConcern implements EncryptionService
{
    public function encryptedValue($aPlainTextValue)
    {
        $this->assertArgumentNotEmpty(
            $aPlainTextValue,
            'Plain text value to encrypt must be provided.'
        );

        return md5($aPlainTextValue);
    }
}
