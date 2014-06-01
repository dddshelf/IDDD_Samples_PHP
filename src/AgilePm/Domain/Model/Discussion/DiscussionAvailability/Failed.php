<?php

namespace SaasOvation\AgilePm\Domain\Model\Discussion\DiscussionAvailability;

class Failed extends DiscussionAvailability
{
    /**
     * @return bool
     */
    public function isFailed() {
        return true;
    }
}
