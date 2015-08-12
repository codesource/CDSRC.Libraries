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

use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model\IpTraceable as Entity;
use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\IpTraceableRepository as EntityRepository;
use CDSRC\Libraries\Traceable\Utility\GeneralUtility;

/**
 * Testcase for entity delete and recover
 *
 */
class IpTraceableTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @var \CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\IpTraceableRepository
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
    public function testIpTracing(){
        $ip = GeneralUtility::getRemoteAddr();
        
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
