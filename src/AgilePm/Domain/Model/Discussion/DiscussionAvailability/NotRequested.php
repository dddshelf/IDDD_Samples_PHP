<?php

namespace SaasOvation\AgilePm\Domain\Model\Discussion\DiscussionAvailability;

class NotRequested extends DiscussionAvailability
{
    /**
     * @return bool
     */
    public function isNotRequested() {
        return true;
    }
}
