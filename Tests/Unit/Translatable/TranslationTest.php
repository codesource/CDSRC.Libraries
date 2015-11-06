<?php

namespace CDSRC\Libraries\Tests\Unit\Translatable;

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

use CDSRC\Libraries\Tests\Unit\Translatable\Fixture\Model\Generic;
use CDSRC\Libraries\Tests\Unit\Translatable\Fixture\Model\Specific;
use TYPO3\Flow\Tests\UnitTestCase;

/**
 * Test Case for the translatable abstract class
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class TranslationTest extends UnitTestCase
{

    /**
     * @test
     */
    public function loadGenericTranslationClassForTranslatableEntity()
    {
        $object = new Generic();
        $translationClass = $object->getTranslationClassName();
        $this->assertEquals('CDSRC\\Libraries\\Translatable\\Domain\\Model\\GenericTranslation', $translationClass);
    }

    /**
     * @test
     */
    public function loadSpecificTranslationClassForTranslatableEntity()
    {
        $object = new Specific();
        $translationClass = $object->getTranslationClassName();
        $this->assertEquals('CDSRC\\Libraries\\Tests\Unit\\Translatable\\Fixture\\Model\\SpecificTranslation', $translationClass);
    }
}
