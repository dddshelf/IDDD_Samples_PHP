<?php

namespace SaasOvation\Common\Port\Adapter\Messaging;

use Exception;
use RuntimeException;

/**
 * I am a basic messaging RuntimeException.
 *
 * @author Vaughn Vernon
 */
class MessageException extends RuntimeException
{
    /**
     * My retry indicator.
     *
     * @var bool
     */
    private $retry;

    /**
     * Constructs my default state.
     *
     * @param string $aMessage the String message
     * @param Exception $aCause the Throwable cause
     * @param bool $isRetry the boolean indicating whether or not to retry sending
     */
    public function __construct($aMessage, Exception $aCause = null, $isRetry = null)
    {
        parent::__construct($aMessage, 0, $aCause);

        if (null !== $isRetry) {
            $this->setRetry($isRetry);
        }
    }

    /**
     * Answers whether or not retry is set. Retry can be
     * used by a MessageListener when it wants the message
     * it has attempted to handle to be re-queued rather than
     * rejected, so that it can re-attempt handling later.
     *
     * @return boolean
     */
    public function isRetry()
    {
        return $this->retry;
    }

    /**
     * Sets my retry.
     *
     * @param bool $aRetry the boolean to set as my retry
     */
    private function setRetry($aRetry)
    {
        $this->retry = $aRetry;
    }
}
