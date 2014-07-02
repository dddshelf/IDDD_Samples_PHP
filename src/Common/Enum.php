<?php

namespace SaasOvation\Common;

use InvalidArgumentException;
use Verraes\ClassFunctions\ClassFunctions;

class Enum
{
    public static function valueOf($anEnum)
    {
        $aClassName = get_called_class() . sprintf('\\%s', $anEnum);

        if (!class_exists($aClassName)) {
            throw new InvalidArgumentException(
                sprintf('The repeat type "%s" is unknown', $anEnum)
            );
        }

        return new $aClassName();
    }

    public function name()
    {
        return ClassFunctions::short($this);
    }
}