<?php

namespace SaasOvation\AgilePm\Domain\Model\Discussion\DiscussionAvailability;

class Requested extends DiscussionAvailability
{
    /**
     * @return bool
     */
    public function isRequested() {
        return true;
    }
}
