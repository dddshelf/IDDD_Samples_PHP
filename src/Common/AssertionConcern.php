<?php

namespace SaasOvation\Common;

class AssertionConcern
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $aString
     * @param string $aMessage
     * @throws \IllegalArgumentException
     */
    protected function assertArgumentNotEmpty($aString, $aMessage) {
        if (null === $aString || empty(trim($aString))) {
            throw new \IllegalArgumentException($aMessage);
        }
    }

    /**
     * @param string $aString
     * @param int $aMaximum
     * @param string $aMessage
     * @throws \IllegalArgumentException
     */
    protected function assertArgumentLength($aString, $aMaximum, $aMessage) {
        $length = strlen(trim($aString));
        if ($length > $aMaximum) {
            throw new \IllegalArgumentException($aMessage);
        }
    }

}
