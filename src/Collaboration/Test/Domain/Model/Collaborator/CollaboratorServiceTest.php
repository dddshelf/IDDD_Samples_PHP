<?php

namespace SaasOvation\Collaboration\Test\Domain\Model\Collaborator;

use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Collaborator\CollaboratorService;
use SaasOvation\Collaboration\Test\Domain\Model\DomainTest;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Collaboration\Port\Adapter\service\CollaboratorTranslator;
use SaasOvation\Collaboration\Port\Adapter\Service\TranslatingCollaboratorService;

class CollaboratorServiceTest extends DomainTest
{
    public static $USER_IN_ROLE_REPRESENTATION = <<<EOR
{
    "role": "UNUSED", "username": "zoe",
    "tenantId": "A94A8298-43B8-4DA0-9917-13FFF9E116ED",
    "firstName": "Zoe", "lastName": "Doe",
    "emailAddress": "zoe@saasovation.com"
}
EOR;

    /**
     * @var CollaboratorService
     */
    private $collaboratorService;

    public function testAuthorFrom()
    {
        $author = $this->collaboratorService->authorFrom(
            new Tenant('12345'),
            'zdoe'
        );

        $this->assertNotNull($author);
    }

    public function testCreatorFrom()
    {
        $creator = $this->collaboratorService->creatorFrom(
            new Tenant('12345'),
            'zdoe'
        );

        $this->assertNotNull($creator);
    }

    public function testModeratorFrom()
    {
        $moderator = $this->collaboratorService->moderatorFrom(
            new Tenant('12345'),
            'zdoe'
        );

        $this->assertNotNull($moderator);
    }

    public function testOwnerFrom()
    {
        $owner = $this->collaboratorService->ownerFrom(
            new Tenant('12345'),
            'zdoe'
        );

        $this->assertNotNull($owner);
    }

    public function testParticipantFrom()
    {
        $participant = $this->collaboratorService->participantFrom(
            new Tenant('12345'),
            'zdoe'
        );

        $this->assertNotNull($participant);
    }

    public function testCollaboratorTranslator()
    {
        $collaborator = (new CollaboratorTranslator())->toCollaboratorFromRepresentation(
            self::$USER_IN_ROLE_REPRESENTATION,
            Author::class
        );

        $this->assertNotNull($collaborator);
        $this->assertEquals('zoe', $collaborator->identity());
        $this->assertEquals('zoe@saasovation.com', $collaborator->emailAddress());
        $this->assertEquals('Zoe Doe', $collaborator->name());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->collaboratorService = new TranslatingCollaboratorService(
            new MockUserInRoleAdapter()
        );
    }
}
