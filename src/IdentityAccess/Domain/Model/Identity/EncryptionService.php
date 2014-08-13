<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

interface EncryptionService
{
    /**
     * @param string $aPlainTextValue
     *
     * @return string
     */
    public function encryptedValue($aPlainTextValue);
}
