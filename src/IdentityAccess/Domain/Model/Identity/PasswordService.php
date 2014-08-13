<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\AssertionConcern;
use RandomLib\Factory as RandomLibFactory;
use SecurityLib\Strength;

final class PasswordService extends AssertionConcern
{
    private static $DIGITS = '0123456789';
    private static $LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private static $STRONG_THRESHOLD = 20;
    private static $SYMBOLS = '/"`!?$?%^&*()_-+={[}]:;@\'~#|\\<,>.?\//';
    private static $VERY_STRONG_THRESHOLD = 40;

    public function generateStrongPassword()
    {
        $password = '';

        $factory = new RandomLibFactory();
        $generator = $factory->getGenerator(new Strength(Strength::MEDIUM));

        $isStrong = false;

        $index = 0;

        while (!$isStrong) {

            $opt = $generator->generateInt(0, 3);

            switch ($opt) {

                case 0:
                    $index = $generator->generateInt(0, strlen(self::$LETTERS) - 1);
                    $password .= substr(self::$LETTERS, $index, $index + 1);
                    break;

                case 1:
                    $index = $generator->generateInt(0, strlen(self::$LETTERS) - 1);
                    $password .= strtolower(substr(self::$LETTERS, $index, $index + 1));
                    break;

                case 2:
                    $index = $generator->generateInt(0, strlen(self::$DIGITS) - 1);
                    $password .= substr(self::$DIGITS, $index, $index+1);
                    break;

                case 3:
                    $index = $generator->generateInt(0, strlen(self::$SYMBOLS) - 1);
                    $password .= substr(self::$SYMBOLS, $index, $index + 1);
                    break;

            }

            if (strlen($password) >= 7) {
                $isStrong = $this->isStrong($password);
            }
        }

        return $password;
    }

    public function isStrong($aPlainTextPassword)
    {
        return $this->calculatePasswordStrength($aPlainTextPassword) >= self::$STRONG_THRESHOLD;
    }

    public function isVeryStrong($aPlainTextPassword)
    {
        return $this->calculatePasswordStrength($aPlainTextPassword) >= self::$VERY_STRONG_THRESHOLD;
    }

    public function isWeak($aPlainTextPassword)
    {
        return $this->calculatePasswordStrength($aPlainTextPassword) < self::$STRONG_THRESHOLD;
    }

    private function calculatePasswordStrength($aPlainTextPassword)
    {
        $this->assertArgumentNotNull($aPlainTextPassword, 'Password strength cannot be tested on null.');

        $strength = 0;

        $length = strlen($aPlainTextPassword);

        if ($length > 7) {
            $strength += 10;
            // bonus: one point each additional
            $strength += ($length - 7);
        }

        $digitCount = $letterCount = $lowerCount = $upperCount = $symbolCount = 0;

        for ($idx = 0; $idx < $length; ++$idx) {

            $ch = $aPlainTextPassword{$idx};

            if (ctype_alnum($ch)) {
                ++$letterCount;
                if (ctype_upper($ch)) {
                    ++$upperCount;
                } else {
                    ++$lowerCount;
                }
            } elseif (ctype_digit($ch)) {
                ++$digitCount;
            } else {
                ++$symbolCount;
            }
        }

        $strength += ($upperCount + $lowerCount + $symbolCount);

        // bonus: letters and digits
        if ($letterCount >= 2 && $digitCount >= 2) {
            $strength += ($letterCount + $digitCount);
        }

        return $strength;
    }
}
