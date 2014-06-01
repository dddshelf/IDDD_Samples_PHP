<?php

namespace SaasOvation\AgilePm\Domain\Model\Discussion;

class Ready extends DiscussionAvailability
{
    /**
     * @return bool
     */
    public function isReady() {
        return true;
    }
}
