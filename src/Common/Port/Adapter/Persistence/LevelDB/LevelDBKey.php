<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\LevelDB;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use UnexpectedValueException;

class LevelDBKey
{
    /**
     * @var string
     */
    private $cachedKey;

    /**
     * @var string
     */
    private $cachedKeyBytes;

    /**
     * @var string
     */
    private $category;

    /**
     * @var int
     */
    private $numberOfSegments;

    /**
     * @var LevelDBKey
     */
    private $primaryKey;

    /**
     * @var Collection
     */
    private $segments;
    
    private function __construct()
    {
        // Dummy constructor
    }

    public static function createFromCategoryAndNumberOfSegments($aCategory, $aNumberOfSegments)
    {
        return static::create(null, $aCategory, $aNumberOfSegments);
    }

    public static function createFromCategoryAndSegments($aCategory)
    {
        $aSegments = array_slice(func_get_args(), 1);
        $anInstance = static::create(null, $aCategory, count($aSegments));

        foreach ($aSegments as $segment) {
            $anInstance->specifyNextSegment($segment);
        }

        return $anInstance;
    }

    public static function createFromPrimaryKey($aPrimaryKey, $aCategory)
    {
        $aSegments = array_slice(func_get_args(), 2);
        $anInstance = static::create($aPrimaryKey, $aCategory, count($aSegments));

        foreach ($aSegments as $segment) {
            $anInstance->specifyNextSegment($segment);
        }

        return $anInstance;
    }

    public static function create($aPrimaryKey, $aCategory, $aNumberOfSegments)
    {
        $anInstance = new LevelDBKey();

        $anInstance->setCategory($aCategory);
        $anInstance->setNumberOfSegments($aNumberOfSegments);
        $anInstance->setPrimaryKey($aPrimaryKey);
        $anInstance->setSegments(new ArrayCollection());

        return $anInstance;
    }

    public function key()
    {
        if (null === $this->cachedKey) {
            $buf = $this->category();

            foreach ($this->segments() as $segment) {
                $buf .= ':' . $segment;
            }

            $this->cachedKey = $buf;
        }

        return $this->cachedKey;
    }

    public function keyAsBytes()
    {
        if (null === $this->cachedKeyBytes) {
            $this->cachedKeyBytes = $this->key();
        }

        return $this->cachedKeyBytes;
    }

    public function primaryKeyValue()
    {
        if (null === $this->primaryKey()) {
            throw new UnexpectedValueException('Unknown primary key.');
        }

        return $this->primaryKey()->key();
    }

    public function specifyNextSegment($aSegment)
    {
        if ($this->segments()->count() === $this->numberOfSegments()) {
            throw new UnexpectedValueException('Specified too many segments.');
        }

        $this->cachedKey = null;
        $this->cachedKeyBytes = null;

        $this->segments()->add($aSegment);
    }

    private function category()
    {
        return $this->category;
    }

    private function setCategory($aCategory)
    {
        if (null === $aCategory || empty(trim($aCategory))) {
            throw new UnexpectedValueException('The category must be provided.');
        }

        $this->category = $aCategory;
    }

    private function numberOfSegments()
    {
        return $this->numberOfSegments;
    }

    private function setNumberOfSegments($aNumberOfSegments)
    {
        $this->numberOfSegments = $aNumberOfSegments;
    }

    private function primaryKey()
    {
        return $this->primaryKey;
    }

    private function setPrimaryKey($aPrimaryKey)
    {
        $this->primaryKey = $aPrimaryKey;
    }

    private function segments()
    {
        return $this->segments;
    }

    private function setSegments(Collection $aSegments)
    {
        $this->segments = $aSegments;
    }
}
