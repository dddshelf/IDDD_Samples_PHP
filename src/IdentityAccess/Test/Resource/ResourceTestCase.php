<?php

namespace SaasOvation\IdentityAccess\Test\Resource;

use SaasOvation\IdentityAccess\Test\BuildsAggregates;
use SaasOvation\IdentityAccess\Test\PreparesApplicationServiceTests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResourceTestCase extends WebTestCase
{
    use PreparesApplicationServiceTests;
    use BuildsAggregates;
}