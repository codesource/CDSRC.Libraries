<?php
namespace CDSRC\Libraries\Tests\Unit\Translatable;

/*
 * Copyright (C) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use CDSRC\Libraries\Tests\Unit\SoftDeletable\Fixture\Model\Entity;

/**
 * Testcase for the translatable trait
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class SoftDeletableTest extends \TYPO3\Flow\Tests\UnitTestCase {
    
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
