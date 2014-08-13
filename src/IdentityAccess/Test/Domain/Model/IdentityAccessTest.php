<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model;

use DateTimeImmutable;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Domain\Model\Identity\ContactInformation;
use SaasOvation\IdentityAccess\Domain\Model\Identity\EmailAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Enablement;
use SaasOvation\IdentityAccess\Domain\Model\Identity\FullName;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Person;
use SaasOvation\IdentityAccess\Domain\Model\Identity\PostalAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Telephone;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Tenant;

abstract class IdentityAccessTest extends DomainTest
{
    protected static $FIXTURE_PASSWORD              = 'SecretPassword!';
    protected static $FIXTURE_TENANT_DESCRIPTION    = 'This is a test tenant.';
    protected static $FIXTURE_TENANT_NAME           = 'Test Tenant';
    protected static $FIXTURE_USER_EMAIL_ADDRESS    = 'jdoe@saasovation.com';
    protected static $FIXTURE_USER_EMAIL_ADDRESS2   = 'zdoe@saasovation.com';
    protected static $FIXTURE_USERNAME              = 'jdoe';
    protected static $FIXTURE_USERNAME2             = 'zdoe';
    protected static $TWENTY_FOUR_HOURS             = 86400000;

    /**
     * @var Tenant
     */
    private $tenant;

    protected function contactInformation()
    {
        return
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
            );
    }

    protected function dayAfterTomorrow()
    {
        return (new DateTimeImmutable())->setTimestamp($this->today()->getTimestamp() + (self::$TWENTY_FOUR_HOURS * 2));
    }

    protected function dayBeforeYesterday()
    {
        return (new DateTimeImmutable())->setTimestamp($this->today()->getTimestamp() - (self::$TWENTY_FOUR_HOURS * 2));
    }

    protected function personEntity(Tenant $aTenant)
    {
        return new Person(
            $aTenant->tenantId(),
            new FullName('John', 'Doe'),
            $this->contactInformation()
        );
    }

    protected function personEntity2(Tenant $aTenant)
    {
        return new Person(
            $aTenant->tenantId(),
            new FullName('Zoe', 'Doe'),
            new ContactInformation(
                new EmailAddress(self::$FIXTURE_USER_EMAIL_ADDRESS2),
                new PostalAddress(
                    '123 Pearl Street',
                    'Boulder',
                    'CO',
                    '80301',
                    'US'),
                new Telephone('303-555-1210'),
                new Telephone('303-555-1212')
            )
        );
    }

    protected function registrationInvitationEntity(Tenant $aTenant)
    {
        $today = new DateTimeImmutable();

        $tomorrow = $today->modify('+1 day');

        $registrationInvitation = $aTenant->offerRegistrationInvitation('Today-and-Tomorrow: ' . microtime(true))
        ->startingOn($today)
        ->until($tomorrow);

        return $registrationInvitation;
    }

    protected function tenantAggregate()
    {
        if (null === $this->tenant) {
            $tenantId = DomainRegistry::tenantRepository()->nextIdentity();

            $this->tenant = new Tenant(
                $tenantId,
                self::$FIXTURE_TENANT_NAME,
                self::$FIXTURE_TENANT_DESCRIPTION,
                true
            );

            DomainRegistry::tenantRepository()->add($this->tenant);
        }

        return $this->tenant;
    }

    protected function today()
    {
        return new DateTimeImmutable();
    }

    protected function tomorrow()
    {
        return $this->today()->modify('+1 day');
    }

    protected function userAggregate()
    {
        $tenant = $this->tenantAggregate();

        $registrationInvitation = $this->registrationInvitationEntity($tenant);

        $user = $tenant->registerUser(
            $registrationInvitation->invitationId(),
            self::$FIXTURE_USERNAME,
            self::$FIXTURE_PASSWORD,
            new Enablement(true, null, null),
            $this->personEntity($tenant)
        );

        return $user;
    }

    protected function userAggregate2()
    {
        $tenant = $this->tenantAggregate();

        $registrationInvitation = $this->registrationInvitationEntity($tenant);

        $user = $tenant->registerUser(
            $registrationInvitation->invitationId(),
            self::$FIXTURE_USERNAME2,
            self::$FIXTURE_PASSWORD,
            new Enablement(true, null, null),
            $this->personEntity2($tenant)
        );

        return $user;
    }

    protected function yesterday()
    {
        return $this->today()->modify('-1 day');
    }
}
