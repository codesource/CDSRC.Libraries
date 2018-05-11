<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Traceable;

use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model\UserTraceable as Entity;
use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\UserTraceableRepository as EntityRepository;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Security\Account;
use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Test case for entity delete and recover
 *
 * @method assetEquals($value, $expected)
 * @method assetNotEquals($value, $expected)
 * @method assetNull($value)
 * @method markTestSkipped($value)
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
     *
     * @throws IllegalObjectTypeException
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
