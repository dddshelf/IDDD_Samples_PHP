<?php

namespace SaasOvation\IdentityAccess\Test\Application;

use PHPUnit_Framework_TestCase;
use SaasOvation\IdentityAccess\Test\BuildsAggregates;
use SaasOvation\IdentityAccess\Test\PreparesApplicationServiceTests;

abstract class ApplicationServiceTest extends PHPUnit_Framework_TestCase
{
    use PreparesApplicationServiceTests;
    use BuildsAggregates;
}
