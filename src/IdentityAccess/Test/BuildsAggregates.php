<?php

namespace SaasOvation\IdentityAccess\Test;

use SaasOvation\Common\Event\EventStore;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Domain\Model\Identity\ContactInformation;
use SaasOvation\IdentityAccess\Domain\Model\Identity\EmailAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Enablement;
use SaasOvation\IdentityAccess\Domain\Model\Identity\FullName;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Person;
use SaasOvation\IdentityAccess\Domain\Model\Identity\PostalAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Telephone;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Tenant;

trait BuildsAggregates
{
    protected static $FIXTURE_GROUP_NAME            = 'Test Group';
    protected static $FIXTURE_PASSWORD              = 'SecretPassword!';
    protected static $FIXTURE_ROLE_NAME             = 'Test Role';
    protected static $FIXTURE_TENANT_DESCRIPTION    = 'This is a test tenant.';
    protected static $FIXTURE_TENANT_NAME           = 'Test Tenant';
    protected static $FIXTURE_USER_EMAIL_ADDRESS    = 'jdoe@saasovation.com';
    protected static $FIXTURE_USER_EMAIL_ADDRESS2   = 'zdoe@saasovation.com';
    protected static $FIXTURE_USERNAME              = 'jdoe';
    protected static $FIXTURE_USERNAME2             = 'zdoe';

    /**
     * @var Tenant
     */
    protected $activeTenant;

    protected function group1Aggregate()
    {
        return $this->tenantAggregate()->provisionGroup(self::$FIXTURE_GROUP_NAME . ' 1', 'A test group 1.');
    }

    protected function group2Aggregate()
    {
        return $this->tenantAggregate()->provisionGroup(self::$FIXTURE_GROUP_NAME . ' 2', 'A test group 2.');
    }

    protected function roleAggregate()
    {
        return $this->tenantAggregate()->provisionRole(self::$FIXTURE_ROLE_NAME, 'A test role.', true);
    }

    protected function tenantAggregate()
    {
        if (null === $this->activeTenant) {

            $this->activeTenant = DomainRegistry::tenantProvisioningService()->provisionTenant(
                self::$FIXTURE_TENANT_NAME,
                self::$FIXTURE_TENANT_DESCRIPTION,
                new FullName('John', 'Doe'),
                new EmailAddress(self::$FIXTURE_USER_EMAIL_ADDRESS),
                new PostalAddress(
                    '123 Pearl Street',
                    'Boulder',
                    'CO',
                    '80301',
                    'US'),
                new Telephone('303-555-1210'),
                new Telephone('303-555-1212')
            );
        }

        return $this->activeTenant;
    }

    protected function userAggregate()
    {
        $tenant = $this->tenantAggregate();

        $invitation = $tenant->offerRegistrationInvitation('open-ended')->openEnded();

        $user = $tenant->registerUser(
            $invitation->invitationId(),
            'jdoe',
            self::$FIXTURE_PASSWORD,
            Enablement::indefiniteEnablement(),
            new Person(
                $tenant->tenantId(),
                new FullName('John', 'Doe'),
                new ContactInformation(
                    new EmailAddress(self::$FIXTURE_USER_EMAIL_ADDRESS),
                    new PostalAddress(
                        '123 Pearl Street',
                        'Boulder',
                        'CO',
                        '80301',
                        'US'),
                    new Telephone('303-555-1210'),
                    new Telephone('303-555-1212')
                )
            )
        );

        return $user;
    }
}