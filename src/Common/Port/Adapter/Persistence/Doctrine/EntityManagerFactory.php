<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

final class EntityManagerFactory
{
    public static function create(array $connectionParams, array $mappingPaths, $isDevMode)
    {
        $config = Setup::createYAMLMetadataConfiguration(
            array_map(
                function ($mappingPath) {
                    return __DIR__ . '/../../../../' . $mappingPath;
                },
                $mappingPaths
            ),
            $isDevMode
        );

        return EntityManager::create($connectionParams, $config);
    }
}