<?php

namespace CDSRC\Libraries\Tests\Functional\Traceable;

/*******************************************************************************
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ******************************************************************************/

use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model\Timestampable;
use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\TimestampableRepository;
use TYPO3\Flow\Persistence\Doctrine\PersistenceManager;
use TYPO3\Flow\Tests\FunctionalTestCase;

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
