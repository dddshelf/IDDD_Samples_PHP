<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Identity;

use Exception;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Domain\Model\Identity\ContactInformation;
use SaasOvation\IdentityAccess\Domain\Model\Identity\EmailAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Enablement;
use SaasOvation\IdentityAccess\Domain\Model\Identity\FullName;
use SaasOvation\IdentityAccess\Domain\Model\Identity\PersonContactInformationChanged;
use SaasOvation\IdentityAccess\Domain\Model\Identity\PersonNameChanged;
use SaasOvation\IdentityAccess\Domain\Model\Identity\PostalAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Telephone;
use SaasOvation\IdentityAccess\Domain\Model\Identity\User;
use SaasOvation\IdentityAccess\Domain\Model\Identity\UserEnablementChanged;
use SaasOvation\IdentityAccess\Domain\Model\Identity\UserPasswordChanged;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;

class UserTest extends IdentityAccessTest
{
    /**
     * @var boolean
     */
    private $handled = false;

    public function setHandled($aValue)
    {
        $this->handled = $aValue;
    }

    public function testUserEnablementEnabled()
    {
        $user = $this->userAggregate();

        $this->assertTrue($user->isEnabled());
    }

    public function testUserEnablementDisabled()
    {
        $user = $this->userAggregate();

        DomainEventPublisher::instance()->subscribe(new UserEnablementChangedSubscriber($this, $user));

        $user->defineEnablement(new Enablement(false, null, null));

        $this->assertFalse($user->isEnabled());
        $this->assertTrue($this->handled);
    }

    public function testUserEnablementWithinStartEndDates()
    {
        $user = $this->userAggregate();

        DomainEventPublisher::instance()->subscribe(new UserEnablementChangedSubscriber($this, $user));

        $user->defineEnablement(
            new Enablement(
                true,
                $this->today(),
                $this->tomorrow()
            )
        );

        $this->assertTrue($user->isEnabled());
        $this->assertTrue($this->handled);
    }

    public function testUserEnablementOutsideStartEndDates()
    {
        $user = $this->userAggregate();

        DomainEventPublisher::instance()->subscribe(new UserEnablementChangedSubscriber($this, $user));

        $user->defineEnablement(
            new Enablement(
                true,
                $this->dayBeforeYesterday(),
                $this->yesterday()
            )
        );

        $this->assertFalse($user->isEnabled());
        $this->assertTrue($this->handled);
    }

    public function testUserEnablementUnsequencedDates()
    {
        $user = $this->userAggregate();

        DomainEventPublisher::instance()->subscribe(new UserEnablementChangedSubscriber($this, $user));

        $failure = false;

        try {
            $user->defineEnablement(
                new Enablement(
                    true,
                    $this->tomorrow(),
                    $this->today()
                )
            );
        } catch (Exception $e) {
            $failure = true;
        }

        $this->assertTrue($failure);
        $this->assertFalse($this->handled);
    }

    public function testUserDescriptor()
    {
        $user = $this->userAggregate();

        $userDescriptor = $user->userDescriptor();

        $this->assertNotNull($userDescriptor->emailAddress());
        $this->assertEquals($userDescriptor->emailAddress(), self::$FIXTURE_USER_EMAIL_ADDRESS);

        $this->assertNotNull($userDescriptor->tenantId());
        $this->assertEquals($userDescriptor->tenantId(), $user->tenantId());

        $this->assertNotNull($userDescriptor->username());
        $this->assertEquals($userDescriptor->username(), self::$FIXTURE_USERNAME);
    }

    public function testUserChangePassword()
    {
        $user = $this->userAggregate();

        DomainEventPublisher::instance()->subscribe(new UserPasswordChangedSubscriber($this, $user));

        $user->changePassword(self::$FIXTURE_PASSWORD, "ThisIsANewPassword.");

        $this->assertTrue($this->handled);
    }

    public function testUserChangePasswordFails()
    {
        $user = $this->userAggregate();

        try {

            $user->changePassword('no clue', "ThisIsANewP4ssw0rd.");

            $this->assertEquals(self::$FIXTURE_PASSWORD, "no clue");

        } catch (Exception $e) {
            // good path, fall through
        }
    }

    public function testUserPasswordHashedOnConstruction()
    {
        $user = $this->userAggregate();

        $this->assertNotEquals(self::$FIXTURE_PASSWORD, $user->password());
    }

    public function testUserPasswordHashedOnChange()
    {
        $user = $this->userAggregate();

        $strongPassword = DomainRegistry::passwordService()->generateStrongPassword();

        $user->changePassword(self::$FIXTURE_PASSWORD, $strongPassword);

        $this->assertNotEquals(self::$FIXTURE_PASSWORD, $user->password());
        $this->assertNotEquals($strongPassword, $user->password());
    }

    public function testUserPersonalContactInformationChanged()
    {
        $user = $this->userAggregate();

        DomainEventPublisher::instance()->subscribe(new PersonContactInformationChangedSubscriber($this, $user));

        $user->changePersonalContactInformation(
            new ContactInformation(
                new EmailAddress(self::$FIXTURE_USER_EMAIL_ADDRESS2),
                new PostalAddress(
                    '123 Mockingbird Lane',
                    'Boulder',
                    'CO',
                    '80301',
                    'US'),
                new Telephone('303-555-1210'),
                new Telephone('303-555-1212')
            )
        );

        $this->assertEquals(new EmailAddress(self::$FIXTURE_USER_EMAIL_ADDRESS2), $user->person()->emailAddress());
        $this->assertEquals('123 Mockingbird Lane', $user->person()->contactInformation()->postalAddress()->streetAddress());
        $this->assertTrue($this->handled);
    }

    public function testUserPersonNameChanged()
    {
        $user = $this->userAggregate();

        DomainEventPublisher::instance()->subscribe(new PersonNameChangedSubscriber($this, $user));

        $user->changePersonalName(new FullName('Joe', 'Smith'));

        $this->assertTrue($this->handled);
    }
}

class UserEnablementChangedSubscriber implements DomainEventSubscriber
{
    /**
     * @var UserTest
     */
    private $test;

    /**
     * @var User
     */
    private $user;

    public function __construct($test, $user)
    {
        $this->test = $test;
        $this->user = $user;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->assertEquals($aDomainEvent->username(), $this->user->username());
        $this->test->setHandled(true);
    }

    public function subscribedToEventType()
    {
        return UserEnablementChanged::class;
    }
}

class UserPasswordChangedSubscriber implements DomainEventSubscriber
{
    /**
     * @var UserTest
     */
    private $test;

    /**
     * @var User
     */
    private $user;

    public function __construct($test, $user)
    {
        $this->test = $test;
        $this->user = $user;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->assertEquals($aDomainEvent->username(), $this->user->username());
        $this->test->assertEquals($aDomainEvent->tenantId(), $this->user->tenantId());
        $this->test->setHandled(true);
    }

    public function subscribedToEventType()
    {
        return UserPasswordChanged::class;
    }
}

class PersonContactInformationChangedSubscriber implements DomainEventSubscriber
{
    /**
     * @var UserTest
     */
    private $test;

    /**
     * @var User
     */
    private $user;

    public function __construct($test, $user)
    {
        $this->test = $test;
        $this->user = $user;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->assertEquals($aDomainEvent->username(), $this->user->username());
        $this->test->setHandled(true);
    }

    public function subscribedToEventType()
    {
        return PersonContactInformationChanged::class;
    }
}

class PersonNameChangedSubscriber implements DomainEventSubscriber
{
    /**
     * @var UserTest
     */
    private $test;

    /**
     * @var User
     */
    private $user;

    public function __construct($test, $user)
    {
        $this->test = $test;
        $this->user = $user;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->assertEquals($aDomainEvent->username(), $this->user->username());
        $this->test->assertEquals($aDomainEvent->name()->firstName(), 'Joe');
        $this->test->assertEquals($aDomainEvent->name()->lastName(), 'Smith');
        $this->test->setHandled(true);
    }

    public function subscribedToEventType()
    {
        return PersonNameChanged::class;
    }
}