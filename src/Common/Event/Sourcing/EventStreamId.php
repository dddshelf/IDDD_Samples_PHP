<?php

namespace SaasOvation\Common\Event\Sourcing;

final class EventStreamId
{
    /**
     * @var string
     */
    private $streamName;

    /**
     * @var int
     */
    private $streamVersion;

    public function __construct($aStreamName, $aStreamVersion = 1)
    {
        $this->setStreamName($aStreamName);
        $this->setStreamVersion($aStreamVersion);
    }

    public function streamName()
    {
        return $this->streamName;
    }

    public function streamVersion()
    {
        return $this->streamVersion;
    }

    public function withStreamVersion($aStreamVersion)
    {
        return new EventStreamId($this->streamName(), $aStreamVersion);
    }

    private function setStreamName($aStreamName)
    {
        $this->streamName = $aStreamName;
    }

    private function setStreamVersion($aStreamVersion)
    {
        $this->streamVersion = $aStreamVersion;
    }
}
