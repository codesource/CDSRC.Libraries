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

use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model\UserTraceable as Entity;
use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\UserTraceableRepository as EntityRepository;

/**
 * Testcase for entity delete and recover
 *
 */
class UserTraceableTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @var boolean
	 */
	protected $testableSecurityEnabled = TRUE;

	/**
	 * @var \CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\UserTraceableRepository
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
    public function testUserTracing(){
		$account = new \TYPO3\Flow\Security\Account();
		$account->setAccountIdentifier('testUserTracing');
		$account->setAuthenticationProviderName('TestingProvider');
        $this->persistenceManager->add($account);
        $this->persistenceManager->persistAll();
        $this->authenticateAccount($account);
        
        $entity = new Entity();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();
        $this->assertEquals($entity->getCreatedBy(), $account);
        $this->assertNull($entity->getUpdatedBy());
        
        $entity->setType('testUserTracing');
        $this->entityRepository->update($entity);
        $this->persistenceManager->persistAll();
        $this->assertEquals($entity->getUpdatedBy(), $account);
        
		$account2 = new \TYPO3\Flow\Security\Account();
		$account2->setAccountIdentifier('testUserTracing2');
		$account2->setAuthenticationProviderName('TestingProvider');
        $this->persistenceManager->add($account2);
        $this->persistenceManager->persistAll();
        $this->authenticateAccount($account2);
        
        $entity->setType('testUserTracing2');
        $this->entityRepository->update($entity);
        $this->persistenceManager->persistAll();
        $this->assertEquals($entity->getUpdatedBy(), $account2);
        $this->assertNotEquals($entity->getCreatedBy(), $account2);
    }
}
