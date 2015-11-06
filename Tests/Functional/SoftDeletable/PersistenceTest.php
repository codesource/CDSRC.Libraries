<?php

namespace CDSRC\Libraries\Tests\Functional\SoftDeletable;

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

use CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Model\Entity;
use CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Model\Entity2;
use CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Repository\Entity2Repository;
use CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Repository\EntityRepository;
use TYPO3\Flow\Persistence\Doctrine\PersistenceManager;
use TYPO3\Flow\Tests\FunctionalTestCase;

/**
 * Test case for entity delete and recover
 *
 */
class PersistenceTest extends FunctionalTestCase
{

    /**
     * @var boolean
     */
    static protected $testablePersistenceEnabled = true;

    /**
     * @var \CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Repository\EntityRepository
     */
    protected $entityRepository;

    /**
     * @var \CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Repository\Entity2Repository
     */
    protected $entity2Repository;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        if (!$this->persistenceManager instanceof PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }
        $this->entityRepository = new EntityRepository();
        $this->entity2Repository = new Entity2Repository();
    }

    /**
     * @test
     */
    public function softDeleteEntity()
    {
        $entity = new Entity();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();

        $this->assertEquals(1, $this->entityRepository->countAll());

        $this->entityRepository->remove($entity);
        $this->persistenceManager->persistAll();

        $this->assertTrue($entity->isDeleted());
        $this->persistenceManager->clearState();
    }

    /**
     * @test
     */
    public function deletedEntitiesAreNotInSelectQueries()
    {
        $entity = new Entity();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();

        $this->entityRepository->remove($entity);
        $this->persistenceManager->persistAll();

        $this->assertEquals(0, $this->entityRepository->countAll());
        $this->persistenceManager->clearState();
    }

    /**
     * @test
     */
    public function futureDeletedEntitiesAreStillSelectable()
    {
        $entity = new Entity();
        $entity2 = new Entity2();
        $this->entityRepository->add($entity);
        $this->entity2Repository->add($entity2);
        $this->persistenceManager->persistAll();

        $this->assertEquals(2, $this->entityRepository->countAll() + $this->entity2Repository->countAll());

        $entity->setDeletedAt(new \DateTime("now + 3 days"));
        $entity2->setDeletedAt(new \DateTime("now + 3 days"));
        $this->entityRepository->update($entity);
        $this->entity2Repository->update($entity2);
        $this->persistenceManager->persistAll();

        $this->assertEquals(1, $this->entityRepository->countAll() + $this->entity2Repository->countAll());
        $this->persistenceManager->clearState();
    }

    /**
     * @test
     */
    public function disableCheckForEntity()
    {
        $entity = new Entity();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();

        $this->entityRepository->remove($entity);
        $this->persistenceManager->persistAll();
        $this->assertEquals(1, $this->entityRepository->allowDeleted()->countAll());
        $this->persistenceManager->clearState();
    }
}
