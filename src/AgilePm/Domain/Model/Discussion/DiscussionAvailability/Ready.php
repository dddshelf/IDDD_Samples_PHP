<?php

namespace SaasOvation\AgilePm\Domain\Model\Discussion\DiscussionAvailability;

class Ready extends DiscussionAvailability
{
    /**
     * @return bool
     */
    public function isReady() {
        return true;
    }
}
