<?php

namespace SaasOvation\AgilePm\Domain\Model\Discussion;

class NotRequested extends DiscussionAvailability
{
    /**
     * @return bool
     */
    public function isNotRequested() {
        return true;
    }
}
