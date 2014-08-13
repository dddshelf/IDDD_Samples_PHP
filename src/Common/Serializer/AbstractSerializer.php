<?php

namespace SaasOvation\Common\Serializer;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

class AbstractSerializer
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct()
    {
        $this->build();
    }

    protected function serializer()
    {
        return $this->serializer;
    }

    private function build()
    {
        $baseDir = __DIR__ . '/../..';

        $this->serializer =
            SerializerBuilder::create()
                ->addDefaultHandlers()
                ->configureHandlers(function (HandlerRegistry $registry) {
                    $registry->registerHandler('serialization', 'DateTime', 'json',
                        function ($visitor, DateTimeInterface $obj, array $type) {
                            return $obj->getTimestamp();
                        }
                    );

                    $registry->registerHandler('deserialization', 'DateTime', 'json',
                        function ($visitor, $aTimeStamp, array $type) {
                            return (new DateTime())->setTimestamp($aTimeStamp);
                        }
                    );

                    $registry->registerHandler('serialization', 'DateTimeImmutable', 'json',
                        function ($visitor, DateTimeInterface $obj, array $type) {
                            return $obj->getTimestamp();
                        }
                    );

                    $registry->registerHandler('deserialization', 'DateTimeImmutable', 'json',
                        function ($visitor, $aTimeStamp, array $type) {
                            return (new DateTimeImmutable())->setTimestamp($aTimeStamp);
                        }
                    );
                })
                ->addDefaultDeserializationVisitors()
                ->addDefaultListeners()
                ->addDefaultSerializationVisitors()
                ->setCacheDir($baseDir . '/../var/cache/jms_serializer')
                ->setDebug(false)
                ->addMetadataDirs([
                    'SaasOvation\\Common' => $baseDir . '/Common/Resources/config/serializer',
                    'SaasOvation\\IdentityAccess' => $baseDir . '/IdentityAccess/Resources/config/serializer'
                ])
            ->build()
        ;
    }
}
