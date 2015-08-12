<?php
namespace CDSRC\Libraries\Tests\Functional\Traceable;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model\Entity;
use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\EntityRepository;

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
	 * @var \CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\EntityRepository
	 */
	protected $entityRepository;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp(); 
		if (!$this->persistenceManager instanceof \TYPO3\Flow\Persistence\Doctrine\PersistenceManager) {
			$this->markTestSkipped('Doctrine persistence is not enabled');
		}
        $this->entityRepository = new EntityRepository();
	}
    
    /**
     * @test
     */
    public function testOnCreateEvent(){
        $entity = new Entity();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();
        $this->assertNotNull($entity->getCreatedAt());
        $this->assertNull($entity->getUpdatedAt());
    }
    
    /**
     * @test
     */
    public function testOnUpdateEvent(){
        $entity = new Entity();
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
    public function testOnChangeEvent(){
        $entity = new Entity();
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
    
    /**
     * @test
     */
    public function testIpTracing(){
        $ip = \CDSRC\Libraries\Traceable\Utility\GeneralUtility::getRemoteAddr();
        
        $entity = new Entity();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();
        $this->assertEquals($entity->getCreatedFromIp(), $ip);
        
        $entity->setType('testIpTracing');
        $this->entityRepository->update($entity);
        $this->persistenceManager->persistAll();
        $this->assertEquals($entity->getUpdatedFromIp(), $ip);
    }
}
