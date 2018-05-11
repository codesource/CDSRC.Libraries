<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\SoftDeletable;

use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Test case for entity reflection testing
 *
 * @method assertTrue($value)
 */
class ReflectionTest extends FunctionalTestCase
{

    /**
     * @var \Neos\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->reflectionService = $this->objectManager->get('Neos\Flow\Reflection\ReflectionService');
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
