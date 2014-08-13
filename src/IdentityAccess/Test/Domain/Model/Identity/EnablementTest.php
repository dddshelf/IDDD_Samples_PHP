<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Identity;

use Exception;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Enablement;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;

class EnablementTest extends IdentityAccessTest
{
    public function testEnablementEnabled()
    {
        $enablement = new Enablement(true, null, null);

        $this->assertTrue($enablement->isEnablementEnabled());
    }

    public function testEnablementDisabled()
    {
        $enablement = new Enablement(false, null, null);

        $this->assertFalse($enablement->isEnablementEnabled());
    }

    public function testEnablementOutsideStartEndDates()
    {
        $enablement = new Enablement(
            true,
            $this->dayBeforeYesterday(),
            $this->yesterday()
        );

        $this->assertFalse($enablement->isEnablementEnabled());
    }

    public function testEnablementUnsequencedDates()
    {
        $failure = false;

        try {
            new Enablement(
                true,
                $this->tomorrow(),
                $this->today());
        } catch (Exception $e) {
            $failure = true;
        }

        $this->assertTrue($failure);
    }

    public function testEnablementEndsTimeExpired()
    {
        $enablement = new Enablement(
            true,
            $this->dayBeforeYesterday(),
            $this->yesterday()
        );

        $this->assertTrue($enablement->isTimeExpired());
    }

    public function testEnablementHasNotBegunTimeExpired()
    {
        $enablement = new Enablement(
            true,
            $this->tomorrow(),
            $this->dayAfterTomorrow()
        );

        $this->assertTrue($enablement->isTimeExpired());
    }
}
