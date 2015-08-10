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
use CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Repository\EntityRepository;

/**
 * Testcase for entity delete and recover
 *
 */
class ReflectionTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp(); 
		$this->reflectionService = $this->objectManager->get('TYPO3\Flow\Reflection\ReflectionService');
	}
    
    /**
     * @test
     */
    public function checkEntityHasAnnotation(){
        $className = 'CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Model\Entity';
        $annotation = 'CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable';
        $this->assertTrue($this->reflectionService->isClassAnnotatedWith($className, $annotation));
    }
}
