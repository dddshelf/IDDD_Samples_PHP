<?php

namespace SaasOvation\Common\Test\Port\Adapter\Persistence\LevelDB;

class Level
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $value;

    public function __construct($anId, $aName, $aValue)
    {
        $this->id = $anId;
        $this->name = $aName;
        $this->value = $aValue;
    }

    public function id()
    {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }

    public function value()
    {
        return $this->value;
    }
}
