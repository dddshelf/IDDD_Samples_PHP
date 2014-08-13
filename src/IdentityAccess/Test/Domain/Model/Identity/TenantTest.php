<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Identity;

use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Domain\Model\Identity\EmailAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Enablement;
use SaasOvation\IdentityAccess\Domain\Model\Identity\FullName;
use SaasOvation\IdentityAccess\Domain\Model\Identity\PostalAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Telephone;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantAdministratorRegistered;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantProvisioned;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;

class TenantTest extends IdentityAccessTest
{
    /**
     * @var boolean
     */
    private $handled1 = false;

    /**
     * @var boolean
     */
    private $handled2 = false;

    public function testProvisionTenant()
    {
        DomainEventPublisher::instance()->subscribe(new TenantProvisionedSubscriber($this));
        DomainEventPublisher::instance()->subscribe(new TenantAdministratorRegisteredSubscriber($this));

        $tenant = DomainRegistry::tenantProvisioningService()->provisionTenant(
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

        $this->assertTrue($this->handled1);
        $this->assertTrue($this->handled2);

        $this->assertNotNull($tenant->tenantId());
        $this->assertNotNull($tenant->tenantId()->id());
        $this->assertEquals(36, strlen($tenant->tenantId()->id()));
        $this->assertEquals(self::$FIXTURE_TENANT_NAME, $tenant->name());
        $this->assertEquals(self::$FIXTURE_TENANT_DESCRIPTION, $tenant->description());
    }

    public function testCreateOpenEndedInvitation()
    {
        $tenant = $this->tenantAggregate();

        $tenant->offerRegistrationInvitation('Open-Ended')->openEnded();

        $this->assertNotNull($tenant->redefineRegistrationInvitationAs('Open-Ended'));
    }

    public function testOpenEndedInvitationAvailable()
    {
        $tenant = $this->tenantAggregate();

        $tenant->offerRegistrationInvitation('Open-Ended')->openEnded();

        $this->assertTrue($tenant->isRegistrationAvailableThrough('Open-Ended'));
    }

    public function testClosedEndedInvitationAvailable()
    {
        $tenant = $this->tenantAggregate();

        $tenant->offerRegistrationInvitation('Today-and-Tomorrow')->startingOn($this->today())->until($this->tomorrow());

        $this->assertTrue($tenant->isRegistrationAvailableThrough('Today-and-Tomorrow'));
    }

    public function testClosedEndedInvitationNotAvailable()
    {
        $tenant = $this->tenantAggregate();

        $tenant
            ->offerRegistrationInvitation('Tomorrow-and-Day-After-Tomorrow')
            ->startingOn($this->tomorrow())
            ->until($this->dayAfterTomorrow());

        $this->assertFalse($tenant->isRegistrationAvailableThrough('Tomorrow-and-Day-After-Tomorrow'));
    }

    public function testAvailableInivitationDescriptor()
    {
        $tenant = $this->tenantAggregate();

        $tenant
            ->offerRegistrationInvitation('Open-Ended')
            ->openEnded();

        $tenant
            ->offerRegistrationInvitation('Today-and-Tomorrow')
            ->startingOn($this->today())
            ->until($this->tomorrow());

        $this->assertEquals($tenant->allAvailableRegistrationInvitations()->count(), 2);
    }

    public function testUnavailableInivitationDescriptor()
    {
        $tenant = $this->tenantAggregate();

        $tenant
            ->offerRegistrationInvitation('Tomorrow-and-Day-After-Tomorrow')
            ->startingOn($this->tomorrow())
            ->until($this->dayAfterTomorrow());

        $this->assertEquals($tenant->allUnavailableRegistrationInvitations()->count(), 1);
    }

    public function testRegisterUser()
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

        $this->assertNotNull($user);

        DomainRegistry::userRepository()->add($user);

        $this->assertNotNull($user->enablement());
        $this->assertNotNull($user->person());
        $this->assertNotNull($user->userDescriptor());
    }

    public function setHandled1($aValue)
    {
        $this->handled1 = $aValue;
    }

    public function setHandled2($aValue)
    {
        $this->handled2 = $aValue;
    }
}

class TenantProvisionedSubscriber implements DomainEventSubscriber
{
    /**
     * @var TenantTest
     */
    private $test;

    public function __construct(TenantTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->setHandled1(true);
    }

    public function subscribedToEventType()
    {
        return TenantProvisioned::class;
    }
}

class TenantAdministratorRegisteredSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(TenantTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->setHandled2(true);
    }

    public function subscribedToEventType()
    {
        return TenantAdministratorRegistered::class;
    }
}