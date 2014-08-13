<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Identity;

use SaasOvation\IdentityAccess\Domain\Model\Identity\FullName;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;

class FullNameTest extends IdentityAccessTest
{
    private static $FIRST_NAME           = "Zoe";
    private static $LAST_NAME            = "Doe";
    private static $MARRIED_LAST_NAME    = "Jones-Doe";
    private static $WRONG_FIRST_NAME     = "Zeo";

    public function testChangedFirstName()
    {
        $name = new FullName(self::$WRONG_FIRST_NAME, self::$LAST_NAME);
        $name = $name->withChangedFirstName(self::$FIRST_NAME);
        $this->assertEquals(self::$FIRST_NAME . ' ' . self::$LAST_NAME, $name->asFormattedName());
    }

    public function testChangedLastName()
    {
        $name = new FullName(self::$FIRST_NAME, self::$LAST_NAME);
        $name = $name->withChangedLastName(self::$MARRIED_LAST_NAME);
        $this->assertEquals(self::$FIRST_NAME . ' ' . self::$MARRIED_LAST_NAME, $name->asFormattedName());
    }

    public function testFormattedName()
    {
        $name = new FullName(self::$FIRST_NAME, self::$LAST_NAME);
        $this->assertEquals(self::$FIRST_NAME . ' ' . self::$LAST_NAME, $name->asFormattedName());
    }
}
