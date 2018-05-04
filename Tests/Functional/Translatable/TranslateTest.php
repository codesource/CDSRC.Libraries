<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable;

use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Entity;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Generic;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Specific;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\EntityRepository;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Test case for translation
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class TranslateTest extends FunctionalTestCase
{

    /**
     * @var boolean
     */
    static protected $testablePersistenceEnabled = true;

    /**
     * @var \CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\EntityRepository
     */
    protected $entityRepository;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        if (!$this->persistenceManager instanceof PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }
        $this->entityRepository = new EntityRepository();
    }


    /**
     * dataProvider for translation testing
     */
    public function getTranslationTypes()
    {
        return array(
            array('string', 'is_string', 'string value', 'translated value', 'en-US'),
            array('boolean', 'is_bool', true, false, 'en-US'),
            array('integer', 'is_int', 1, 2, 'en-US'),
            array('float', 'is_float', 1.01, 2.01, 'en-US'),
            array('array', 'is_array', array('test1', 'test2'), array('test3', 'test4'), 'en-US'),
            array('date', array($this, 'isDateTime'), new \DateTime('2015-06-25 22:22:22'), new \DateTime('2016-01-01 22:22:22'), 'en-US'),
        );
    }

    /**
     * Tests object data type against generic translation
     *
     * @test
     */
    public function testObjectOnGenericTranslation()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet with new feature.'
        );
        $entitySource = new Entity('dummy1');
        $entityTranslation = new Entity('dummy2');
        $this->entityRepository->add($entitySource);
        $this->entityRepository->add($entityTranslation);

        $this->testOnGenericTranslation('object', 'is_object', $entitySource, $entityTranslation, 'en-US');
    }

    /**
     * Tests all data type against generic translation
     *
     * @param string $type
     * @param mixed $typeCheckFunction
     * @param mixed $value
     * @param mixed $translatedValue
     * @param string $language
     *
     * @test
     * @dataProvider getTranslationTypes
     */
    public function testOnGenericTranslation($type, $typeCheckFunction, $value, $translatedValue, $language = 'en-US')
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet with new feature.'
        );
        $object = new Generic();
        $object->$type = $value;
        $object->setLocaleForTranslation($language)->$type = $translatedValue;

        if (is_callable($typeCheckFunction)) {
            $this->assertTrue(call_user_func($typeCheckFunction, $object->setLocaleForTranslation($language)->$type));
        }
        $this->assertEquals($object->setLocaleForTranslation($language)->$type, $translatedValue);
        $this->assertNotEquals($object->setLocaleForTranslation($language)->$type, $object->setDefaultLocaleForTranslation()->$type);
    }

    /**
     * Tests object data type against generic translation
     *
     * @test
     */
    public function testObjectOnSpecificTranslation()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet with new feature.'
        );
        $entitySource = new Entity('dummy1');
        $entityTranslation = new Entity('dummy2');
        $this->entityRepository->add($entitySource);
        $this->entityRepository->add($entityTranslation);

        $this->testOnSpecificTranslation('object', 'is_object', $entitySource, $entityTranslation, 'en-US');
    }

    /**
     * Tests all data type against generic translation
     *
     * @param string $type
     * @param mixed $typeCheckFunction
     * @param mixed $value
     * @param mixed $translatedValue
     * @param string $language
     *
     * @test
     * @dataProvider getTranslationTypes
     */
    public function testOnSpecificTranslation($type, $typeCheckFunction, $value, $translatedValue, $language = 'en-US')
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet with new feature.'
        );
        $object = new Specific();
        $object->$type = $value;
        $object->setLocaleForTranslation($language)->$type = $translatedValue;

        if (is_callable($typeCheckFunction)) {
            $this->assertTrue(call_user_func($typeCheckFunction, $object->setLocaleForTranslation($language)->$type));
        }
        $this->assertEquals($object->setLocaleForTranslation($language)->$type, $translatedValue);
        $this->assertNotEquals($object->setLocaleForTranslation($language)->$type, $object->setDefaultLocaleForTranslation()->$type);
    }

    /**
     * Check if object is an instance of DateTime
     *
     * @param \DateTime $object
     *
     * @return boolean
     */
    public function isDateTime($object)
    {
        return is_object($object) && $object instanceof \DateTime;
    }
}
