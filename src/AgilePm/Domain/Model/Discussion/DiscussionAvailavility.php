<?php

namespace SaasOvation\AgilePm\Domain\Model\Discussion;

abstract class DiscussionAvailability
{
    /**
     * @return bool
     */
    public function isAddOnNotAvailable() {
        return false;
    }

    /**
     * @return bool
     */
    public function isFailed() {
        return false;
    }

    /**
     * @return bool
     */
    public function isNotRequested() {
        return false;
    }

    /**
     * @return bool
     */
    public function isReady() {
        return false;
    }

    /**
     * @return bool
     */
    public function isRequested() {
        return false;
    }
}
