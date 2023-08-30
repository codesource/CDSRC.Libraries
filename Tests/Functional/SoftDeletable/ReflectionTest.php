<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\SoftDeletable;

use Neos\Flow\Reflection\ReflectionService;
use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Test case for entity reflection testing
 *
 * @method assertTrue($value)
 */
class ReflectionTest extends FunctionalTestCase
{

    /**
     * @var ReflectionService
     */
    protected ReflectionService $reflectionService;

    /**
     * @return void
     */
    public function setUp(): void
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
