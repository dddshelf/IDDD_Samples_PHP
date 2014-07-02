<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ;

use DateTimeInterface;

/**
 * I am a set of message parameters.
 *
 * @author Vaughn Vernon
 */
class MessageParameters
{
    /**
     * @var array
     */
    private $properties;

    public static function durableTextParameters($aType, $aMessageId, DateTimeInterface $aTimestamp)
    {
        return new MessageParameters([
            'contentType'     => 'text/plain',                   // contentType
            'contentEncoding' => null,                           // contentEncoding
            'headers'         => null,                           // headers
            'deliveryMode'    => 2,                              // deliveryMode, persistent
            'priority'        => 0,                              // priority
            'correlationId'   => null,                           // correlationId
            'replyTo'         => null,                           // replyTo
            'expiration'      => null,                           // expiration
            'messageId'       => $aMessageId,                    // messageId
            'timestamp'       => $aTimestamp->getTimestamp(),    // timestamp
            'type'            => $aType,                         // type
            'userId'          => null,                           // userId
            'appId'           => null,                           // appId
            'clusterId'       => null                            // clusterId
        ]);
    }

    public static function textParameters($aType, $aMessageId, DateTimeInterface $aTimestamp)
    {
        return new MessageParameters([
            'contentType'     => 'text/plain',                   // contentType
            'contentEncoding' => null,                           // contentEncoding
            'headers'         => null,                           // headers
            'deliveryMode'    => 1,                              // deliveryMode, non-persistent
            'priority'        => 0,                              // priority
            'correlationId'   => null,                           // correlationId
            'replyTo'         => null,                           // replyTo
            'expiration'      => null,                           // expiration
            'messageId'       => $aMessageId,                    // messageId
            'timestamp'       => $aTimestamp->getTimestamp(),    // timestamp
            'type'            => $aType,                         // type
            'userId'          => null,                           // userId
            'appId'           => null,                           // appId
            'clusterId'       => null                            // clusterId
        ]);
    }

    public function isDurable()
    {
        $deliveryMode = isset($this->properties()['deliveryMode']) ? $this->properties()['deliveryMode'] : null;

        return (null !== $deliveryMode && $deliveryMode === 2);
    }

    public function properties()
    {
        return $this->properties;
    }

    private function __construct(array $aProperties)
    {
        $this->setProperties($aProperties);
    }

    private function setProperties(array $aProperties)
    {
        $this->properties = $aProperties;
    }
}
