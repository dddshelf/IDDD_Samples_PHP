<?php

namespace SaasOvation\AgilePm\Domain\Model\Discussion;

class Failed extends DiscussionAvailability
{
    /**
     * @return bool
     */
    public function isFailed() {
        return true;
    }
}
