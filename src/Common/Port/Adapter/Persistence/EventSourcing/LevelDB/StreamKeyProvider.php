<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\LevelDB;

class StreamKeyProvider extends JournalKeyProvider
{
    /**
     * @var string
     */
    private $streamName;

    /**
     * @var int
     */
    private $streamVersion;

    public function __construct($aStreamName, $aStartingStreamVersion)
    {
        $this->streamName = $aStreamName;
        $this->streamVersion = $aStartingStreamVersion;
    }

    public function nextReferenceKey()
    {
        $key = $this->compositeReferenceKeyFrom($this->streamName, '' . $this->streamVersion);

        ++$this->streamVersion;

        return $key;
    }

    public function primaryResourceName()
    {
        return $this->streamName;
    }
}
