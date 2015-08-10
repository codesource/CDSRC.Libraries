<?php
namespace CDSRC\Libraries\Tests\Functional\SoftDeletable;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Model\Entity;
use CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Model\Entity2;
use CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Repository\EntityRepository;
use CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Repository\Entity2Repository;

/**
 * Testcase for entity delete and recover
 *
 */
class PersistenceTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

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
	public function setUp() {
		parent::setUp(); 
		if (!$this->persistenceManager instanceof \TYPO3\Flow\Persistence\Doctrine\PersistenceManager) {
			$this->markTestSkipped('Doctrine persistence is not enabled');
		}
        $this->entityRepository = new EntityRepository();
        $this->entity2Repository = new Entity2Repository();
	}
    
    /**
     * @test
     */
    public function softDeleteEntity(){
        $entity = new Entity();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();
        
        $this->assertEquals(1, $this->entityRepository->countAll());
        
        $this->entityRepository->remove($entity);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();
        
        $this->assertTrue($entity->isDeleted());
    }
    
    /**
     * @test
     */
    public function deletedEntitiesAreNotInSelectQueries(){
        $entity = new Entity();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();
        
        $this->entityRepository->remove($entity);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();
        
        $this->assertEquals(0, $this->entityRepository->countAll());
    }
    
    /**
     * @test
     */
    public function futurDeletedEntitiesAreStillSelectable(){
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
        $this->persistenceManager->clearState();
        
        $this->assertEquals(1, $this->entityRepository->countAll() + $this->entity2Repository->countAll());
    }
    
    /**
     * @test
     */
    public function disableCheckForEntity(){
        $entity = new Entity();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();
        
        $this->entityRepository->remove($entity);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();
        
        $this->assertEquals(1, $this->entityRepository->allowDeleted()->countAll());
    }
}
