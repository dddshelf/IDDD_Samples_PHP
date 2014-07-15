<?php

namespace SaasOvation\Common\Test\Port\Adapter\Persistence\LevelDB;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use LevelDB;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBUnitOfWork;
use SaasOvation\Common\Test\Port\Adapter\Persistence\LevelDB\Level;
use SaasOvation\Common\Test\Port\Adapter\Persistence\LevelDB\LevelRepository;

class LevelDBPersistenceTest extends LevelDBTest
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;

    protected function setUp()
    {
        $this->levelRepository = new LevelRepository();

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->levelRepository = null;

        parent::tearDown();
    }

    public function testSaveAndQuery()
    {
        $level1 = new Level('1', 'One', 1);
        $level2 = new Level('2', 'Two', 2);
        $level3 = new Level('3', 'Three', 3);

        LevelDBUnitOfWork::start($this->database());
        $this->levelRepository->save($level1);
        $this->levelRepository->save($level2);
        $this->levelRepository->save($level3);
        LevelDBUnitOfWork::current()->commit();

        $this->assertEquals(3, $this->levelRepository->allLevels()->count());

        $this->assertEquals($level1->id(), $this->levelRepository->levelOfId('1')->id());
        $this->assertEquals($level2->id(), $this->levelRepository->levelOfId('2')->id());
        $this->assertEquals($level3->id(), $this->levelRepository->levelOfId('3')->id());

        $this->assertEquals($level1->name(), $this->levelRepository->levelOfName('One')->name());
        $this->assertEquals($level2->name(), $this->levelRepository->levelOfName('Two')->name());
        $this->assertEquals($level3->name(), $this->levelRepository->levelOfName('Three')->name());

        $this->assertEquals($level1->value(), $this->levelRepository->levelOfId('1')->value());
        $this->assertEquals($level2->value(), $this->levelRepository->levelOfId('2')->value());
        $this->assertEquals($level3->value(), $this->levelRepository->levelOfId('3')->value());
    }

    public function testRemoveAndQuery()
    {
        $level1 = new Level('1', 'One', 1);
        $level2 = new Level('2', 'Two', 2);
        $level3 = new Level('3', 'Three', 3);

        LevelDBUnitOfWork::start($this->database());
        $this->levelRepository->save($level1);
        $this->levelRepository->save($level2);
        $this->levelRepository->save($level3);
        LevelDBUnitOfWork::current()->commit();

        $this->assertEquals(3, $this->levelRepository->allLevels()->count());
        $this->assertEquals($level1->id(), $this->levelRepository->levelOfId('1')->id());
        $this->assertEquals($level2->id(), $this->levelRepository->levelOfId('2')->id());
        $this->assertEquals($level3->id(), $this->levelRepository->levelOfId('3')->id());

        LevelDBUnitOfWork::start($this->database());
        $this->levelRepository->remove($level2);
        LevelDBUnitOfWork::current()->commit();

        $this->assertEquals(2, $this->levelRepository->allLevels()->count());
        $this->assertNull($this->levelRepository->levelOfId('2'));
        $this->assertEquals($level1->id(), $this->levelRepository->levelOfId('1')->id());
        $this->assertEquals($level3->id(), $this->levelRepository->levelOfId('3')->id());

        LevelDBUnitOfWork::start($this->database());
        $this->levelRepository->remove($level1);
        LevelDBUnitOfWork::current()->commit();

        $this->assertEquals(1, $this->levelRepository->allLevels()->count());
        $this->assertNull($this->levelRepository->levelOfId('1'));
        $this->assertNull($this->levelRepository->levelOfId('2'));
        $this->assertEquals($level3->id(), $this->levelRepository->levelOfId('3')->id());

        LevelDBUnitOfWork::start($this->database());
        $this->levelRepository->remove($level3);
        LevelDBUnitOfWork::current()->commit();

        $this->assertTrue($this->levelRepository->allLevels()->isEmpty());
        $this->assertNull($this->levelRepository->levelOfId('1'));
        $this->assertNull($this->levelRepository->levelOfId('2'));
        $this->assertNull($this->levelRepository->levelOfId('3'));
    }
}
