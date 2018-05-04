<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Unit\Translatable;

use CDSRC\Libraries\Tests\Unit\Translatable\Fixture\Model\Generic;
use CDSRC\Libraries\Tests\Unit\Translatable\Fixture\Model\Specific;
use Neos\Flow\Tests\UnitTestCase;

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
