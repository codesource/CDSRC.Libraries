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

use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model\UserTraceable as Entity;
use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\UserTraceableRepository as EntityRepository;
use TYPO3\Flow\Persistence\Doctrine\PersistenceManager;
use TYPO3\Flow\Security\Account;
use TYPO3\Flow\Tests\FunctionalTestCase;

/**
 * Test case for entity delete and recover
 *
 */
class UserTraceableTest extends FunctionalTestCase {

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
		if (!$this->persistenceManager instanceof PersistenceManager) {
			$this->markTestSkipped('Doctrine persistence is not enabled');
		}
        $this->entityRepository = new EntityRepository();
	}
    
    /**
     * @test
     */
    public function testUserTracing(){
		$account = new Account();
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
        
		$account2 = new Account();
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
