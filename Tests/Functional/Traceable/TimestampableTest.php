<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Traceable;

use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model\Timestampable;
use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\TimestampableRepository;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Test case to test Timestampable functionality
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class TimestampableTest extends FunctionalTestCase {

    /**
     * @var boolean
     */
    static protected $testablePersistenceEnabled = TRUE;

    /**
     * @var \CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\TimestampableRepository
     */
    protected $entityRepository;

    /**
     * @return void
     */
    public function setUp() {
        parent::setUp();
        if (!$this->persistenceManager instanceof PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }
        $this->entityRepository = new TimestampableRepository();
    }

    /**
     * @test
     */
    public function testOnCreateEvent() {
        $entity = new Timestampable();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();
        $this->assertNotNull($entity->getCreatedAt());
        $this->assertNull($entity->getUpdatedAt());
    }

    /**
     * @test
     */
    public function testOnUpdateEvent() {
        $entity = new Timestampable();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();
        $this->assertNull($entity->getUpdatedAt());

        $entity->setType('testOnUpdateEventWithDateTime:1');
        $this->entityRepository->update($entity);
        $this->persistenceManager->persistAll();
        $updatedAt = $entity->getUpdatedAt();
        $this->assertNotNull($updatedAt);

        sleep(1);
        $entity->setType('testOnUpdateEventWithDateTime:2');
        $this->entityRepository->update($entity);
        $this->persistenceManager->persistAll();
        $this->assertNotNull($entity->getUpdatedAt());
        $this->assertNotEquals($updatedAt, $entity->getUpdatedAt());
    }

    /**
     * @test
     */
    public function testOnChangeEvent() {
        $entity = new Timestampable();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();
        $this->assertNull($entity->getChangedAt());

        $entity->setType('testOnChangeEventWithDateTime');
        $this->entityRepository->update($entity);
        $this->persistenceManager->persistAll();
        $this->assertNotNull($entity->getChangedAt());

        $entity->setType('value1');
        $this->entityRepository->update($entity);
        $this->persistenceManager->persistAll();
        $changedAtIf = $entity->getChangedAtIf();
        $this->assertNotNull($changedAtIf);

        sleep(1);
        $entity->setType('value3');
        $this->entityRepository->update($entity);
        $this->persistenceManager->persistAll();
        $this->assertEquals($changedAtIf, $entity->getChangedAtIf());

        sleep(2);
        $entity->setType('value2');
        $this->entityRepository->update($entity);
        $this->persistenceManager->persistAll();
        $this->assertNotEquals($changedAtIf, $entity->getChangedAtIf());
    }

}
