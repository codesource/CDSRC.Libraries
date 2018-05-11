<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */
namespace CDSRC\Libraries\Tests\Unit\Translatable;

use CDSRC\Libraries\Tests\Unit\SoftDeletable\Fixture\Model\Entity;
use Neos\Flow\Tests\UnitTestCase;

/**
 * Test case for the translatable trait
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * @method assertEquals($value, $expected)
 * @method assertFalse($value)
 * @method assertTrue($value)
 */
class SoftDeletableTest extends UnitTestCase {
    
    /**
     * @test
     */
    public function checkEntityHasPropertiesAndMethods(){
        $className = 'CDSRC\Libraries\Tests\Unit\SoftDeletable\Fixture\Model\Entity';
        $this->assertTrue(property_exists($className, 'deletedAt'));
        $this->assertTrue(property_exists($className, 'deletedBy'));
        $this->assertTrue(method_exists($className, 'setDeletedAt'));
        $this->assertTrue(method_exists($className, 'getDeletedAt'));
        $this->assertTrue(method_exists($className, 'setDeletedBy'));
        $this->assertTrue(method_exists($className, 'getDeletedBy'));
        $this->assertTrue(method_exists($className, 'getForceDelete'));
        $this->assertTrue(method_exists($className, 'forceHardDelete'));
        $this->assertTrue(method_exists($className, 'isDeleted'));
    }
    
    /**
     * @test
     */
    public function checkNewEntityCorrectlyInitialized(){
        $entity = new Entity();
		$this->assertEquals($entity->getDeletedAt(), NULL);
		$this->assertEquals($entity->getDeletedBy(), NULL);
		$this->assertFalse($entity->isDeleted());
    }
}
