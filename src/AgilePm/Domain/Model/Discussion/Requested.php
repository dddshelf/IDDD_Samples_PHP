<?php

namespace SaasOvation\AgilePm\Domain\Model\Discussion;

class Requested extends DiscussionAvailability
{
    /**
     * @return bool
     */
    public function isRequested() {
        return true;
    }
}
