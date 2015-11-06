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

use TYPO3\Flow\Tests\FunctionalTestCase;

/**
 * Test case for entity reflection testing
 *
 */
class ReflectionTest extends FunctionalTestCase
{

    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->reflectionService = $this->objectManager->get('TYPO3\Flow\Reflection\ReflectionService');
    }

    /**
     * @test
     */
    public function checkEntityHasAnnotation()
    {
        $className = 'CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Model\Entity';
        $annotation = 'CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable';
        $this->assertTrue($this->reflectionService->isClassAnnotatedWith($className, $annotation));
    }
}
