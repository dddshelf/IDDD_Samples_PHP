<?php

namespace SaasOvation\Collaboration\Test\Domain\Model\Collaborator;

use Exception;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Creator;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Owner;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Participant;
use SaasOvation\Collaboration\Test\Domain\Model\DomainTest;

class CollaboratorTest extends DomainTest
{
    public function testAuthorEquals()
    {
        $author1 = new Author('jdoe', 'John Doe', 'jdoe@saasovation.com');
        $author2 = new Author('jdoe', 'John Doe', 'jdoe@saasovation.com');
        $author3 = new Author('zdoe', 'Zoe Doe', 'zdoe@saasovation.com');
        
        $this->assertEquals($author1, $author2);
        $this->assertNotSame($author1, $author2);
        $this->assertFalse($author1->equals($author3));
        $this->assertFalse($author2->equals($author3));
    }

    public function testCreatorEquals()
    {
        $creator1 = new Creator('jdoe', 'John Doe', 'jdoe@saasovation.com');
        $creator2 = new Creator('jdoe', 'John Doe', 'jdoe@saasovation.com');
        $creator3 = new Creator('zdoe', 'Zoe Doe', 'zdoe@saasovation.com');

        $this->assertEquals($creator1, $creator2);
        $this->assertNotSame($creator1, $creator2);
        $this->assertFalse($creator1->equals($creator3));
        $this->assertFalse($creator2->equals($creator3));
    }

    public function testOwnerEquals()
    {
        $owner1 = new Owner('jdoe', 'John Doe', 'jdoe@saasovation.com');
        $owner2 = new Owner('jdoe', 'John Doe', 'jdoe@saasovation.com');
        $owner3 = new Owner('zdoe', 'Zoe Doe', 'zdoe@saasovation.com');

        $this->assertEquals($owner1, $owner2);
        $this->assertNotSame($owner1, $owner2);
        $this->assertFalse($owner1->equals($owner3));
        $this->assertFalse($owner2->equals($owner3));
    }

    public function testParticipantEquals()
    {
        $participant1 = new Participant('jdoe', 'John Doe', 'jdoe@saasovation.com');
        $participant2 = new Participant('jdoe', 'John Doe', 'jdoe@saasovation.com');
        $participant3 = new Participant('zdoe', 'Zoe Doe', 'zdoe@saasovation.com');

        $this->assertEquals($participant1, $participant2);
        $this->assertNotSame($participant1, $participant2);
        $this->assertFalse($participant1->equals($participant3));
        $this->assertFalse($participant2->equals($participant3));
    }

    public function testRoleIdentityLimits()
    {
        $failed = false;

        try {
            new Author('', '', '');

            $this->fail('Should have thrown exception.');

        } catch (Exception $t) {
            $failed = true;
        }

        $this->assertTrue($failed);

        $failed = false;

        try {
            new Author(
                '01234567890123456789012345678901234567890123456789' . 'x',
                'Some Name',
                'doh@saasovation.com');

            $this->fail('Should have thrown exception.');

        } catch (Exception $t) {
            $failed = true;
        }

        $this->assertTrue($failed);
    }
}
