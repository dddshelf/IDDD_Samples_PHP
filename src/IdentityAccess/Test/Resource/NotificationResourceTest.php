<?php

namespace SaasOvation\IdentityAccess\Test\Resource;

use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Notification\NotificationLogReader;
use SaasOvation\IdentityAccess\Application\ApplicationServiceRegistry;
use SaasOvation\IdentityAccess\Application\Command\ChangeEmailAddressCommand;
use SaasOvation\IdentityAccess\Application\Command\ProvisionTenantCommand;
use SaasOvation\IdentityAccess\Application\Command\RegisterUserCommand;
use SaasOvation\IdentityAccess\Application\IdentityAccessEventProcessor;
use SaasOvation\IdentityAccess\Domain\Model\Identity\PersonNameChanged;
use SaasOvation\IdentityAccess\Domain\Model\Identity\UserPasswordChanged;

class NotificationResourceTest extends ResourceTestCase
{
    public function testBasicNotificationLog()
    {
        $this->generateUserEvents();

        $currentNotificationLog = ApplicationServiceRegistry::notificationApplicationService()->currentNotificationLog();

        $this->assertNotNull($currentNotificationLog);

        $client = static::createClient();
        $client->request('GET', '/notifications');

        $aJSONRepresentation = $client->getResponse()->getContent();

        $log = NotificationLogReader::fromString(
            $aJSONRepresentation
        );

        $this->assertFalse($log->isArchived());
        $this->assertNotNull($log->id());

        foreach ($log as $notification) {
            $typeName = $notification->typeName();
            $this->assertTrue(
                false !== strstr($typeName, 'UserRegistered')
                || false !== strstr($typeName, 'PersonNameChanged')
                || false !== strstr($typeName, 'UserPasswordChanged')
            );
        }
    }

    public function testPersonContactInformationChangedNotification()
    {
        $this->generateUserEvents();

        IdentityAccessEventProcessor::register($this->eventStore);

        ApplicationServiceRegistry::identityApplicationService()->changeUserEmailAddress(
            new ChangeEmailAddressCommand(
                $this->tenantAggregate()->tenantId()->id(),
                self::$FIXTURE_USERNAME . 0,
                self::$FIXTURE_USER_EMAIL_ADDRESS2
            )
        );

        $client = static::createClient();
        $client->request('GET', '/notifications');

        $log = NotificationLogReader::fromString(
            $client->getResponse()->getContent()
        );

        $this->assertFalse($log->isArchived());
        $this->assertNotNull($log->id());

        $found = false;

        foreach ($log as $notification) {
            $typeName = $notification->typeName();
            if (false !== strstr($typeName, 'PersonContactInformationChanged')) {
                $this->assertEquals(
                    self::$FIXTURE_USER_EMAIL_ADDRESS2,
                    $notification->eventStringValue('contact_information.email_address')
                );

                $found = true;
            }
        }

        $this->assertTrue($found);
    }

    public function testTenantProvisionedNotification()
    {
        IdentityAccessEventProcessor::register($this->eventStore);

        $newTenant = ApplicationServiceRegistry::identityApplicationService()->provisionTenant(
            new ProvisionTenantCommand(
                'All-Star Tenant',
                'An all-star company.',
                'Frank',
                'Oz',
                'frank@allstartcompany.com',
                '212-555-1211',
                '212-555-1212',
                '123 5th Avenue',
                'New York',
                'NY',
                '11201',
                'US'
            )
        );

        $this->assertNotNull($newTenant);

        $client = static::createClient();
        $client->request('GET', '/notifications');

        $log = NotificationLogReader::fromString(
            $client->getResponse()->getContent()
        );

        $this->assertFalse($log->isArchived());
        $this->assertNotNull($log->id());

        $found = false;

        foreach ($log as $notification) {
            $typeName = $notification->typeName();

            if (false !== strstr($typeName, 'TenantProvisioned')) {
                $tenantId = $notification->eventStringValue("tenant_id.id");

                $this->assertEquals($newTenant->tenantId()->id(), $tenantId);

                $found = true;
            }
        }

        $this->assertTrue($found);
    }

    public function testNotificationNavigation()
    {
        $this->generateUserEvents();

        $client = static::createClient();
        $client->request('GET', '/notifications');

        $aJSONRepresentation = $client->getResponse()->getContent();

        $log = NotificationLogReader::fromString(
            $aJSONRepresentation
        );

        $this->assertFalse($log->isArchived());
        $this->assertNotNull($log->id());

        $this->assertFalse($log->hasNext());
        $this->assertTrue($log->hasSelf());
        $this->assertTrue($log->hasPrevious());

        $count = 0;

        while ($log->hasPrevious()) {
            ++$count;

            $previous = $log->previous();

            $client->request('GET', $previous->getHref());

            $log = NotificationLogReader::fromString(
                $client->getResponse()->getContent()
            );

            $this->assertTrue($log->isArchived());
            $this->assertNotNull($log->id());
            $this->assertTrue($log->hasNext());
            $this->assertTrue($log->hasSelf());
        }

        $this->assertGreaterThanOrEqual(1, $count);
    }

    private function generateUserEvents()
    {
        $tenant = $this->tenantAggregate();
        $person = $this->userAggregate()->person();

        $invitationId = $tenant->allAvailableRegistrationInvitations()->getIterator()->current()->invitationId();

        for ($idx = 0; $idx < 25; ++$idx) {
            $user = ApplicationServiceRegistry::identityApplicationService()->registerUser(
                new RegisterUserCommand(
                    $tenant->tenantId()->id(),
                    $invitationId,
                    self::$FIXTURE_USERNAME . $idx,
                    self::$FIXTURE_PASSWORD,
                    'Zoe',
                    'Doe',
                    true,
                    null,
                    null,
                    $person->contactInformation()->emailAddress()->address(),
                    $person->contactInformation()->primaryTelephone()->number(),
                    $person->contactInformation()->secondaryTelephone()->number(),
                    $person->contactInformation()->postalAddress()->streetAddress(),
                    $person->contactInformation()->postalAddress()->city(),
                    $person->contactInformation()->postalAddress()->stateProvince(),
                    $person->contactInformation()->postalAddress()->postalCode(),
                    $person->contactInformation()->postalAddress()->countryCode()
                )
            );

            if (($idx % 2) == 0) {
                $event = new PersonNameChanged(
                    $user->tenantId(),
                    $user->username(),
                    $user->person()->name()
                );

                $this->eventStore->append($event);
            }

            if (($idx % 3) == 0) {
                $event = new UserPasswordChanged(
                    $user->tenantId(),
                    $user->username()
                );

                $this->eventStore->append($event);
            }

            DomainEventPublisher::instance()->reset();
        }
    }
}
