<?php
namespace CDSRC\Libraries\Tests\Functional\Translatable;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Entity;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\EntityRepository;

/**
 * Testcase for translation
 *
 */
class TranslateTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @var \CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\EntityRepository
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
	 * dataProvider for translation testing
	 */
	public function getTranslationTypes() {
		return array(
            array('string', 'is_string', 'string value', 'translated value', 'en-US'),
            array('boolean', 'is_bool', TRUE, FALSE, 'en-US'),
            array('integer', 'is_int', 1, 2, 'en-US'),
            array('float', 'is_float', 1.01, 2.01, 'en-US'),
            array('array', 'is_array', array('test1', 'test2'), array('test3', 'test4'), 'en-US'),
            array('date', array($this, 'isDateTime'), new \DateTime('2015-06-25 22:22:22'), new \DateTime('2016-01-01 22:22:22'), 'en-US'),
    	);
	}
    
    /**
     * Tests all datatype against generic translation
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
    public function testOnGenericTranslation($type, $typeCheckFunction, $value, $translatedValue, $language = 'en-US'){
        $object = new \CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Generic();
        $object->$type = $value;
        $object->setLocaleForTranslation($language)->$type = $translatedValue;
        
        if(is_callable($typeCheckFunction)){
            $this->assertTrue(call_user_func($typeCheckFunction, $object->setLocaleForTranslation($language)->$type));
        }
		$this->assertEquals($object->setLocaleForTranslation($language)->$type, $translatedValue);
		$this->assertNotEquals($object->setLocaleForTranslation($language)->$type, $object->setDefaultLocaleForTranslation()->$type);
    }
    
    
    /**
     * Tests object datatype against generic translation
     * 
	 * @test
     */
    public function testObjectOnGenericTranslation(){
        $entitySource = new Entity('dummy1');
        $entityTranslation = new Entity('dummy2');
		$this->entityRepository->add($entitySource);
		$this->entityRepository->add($entityTranslation);
        
        $this->testOnGenericTranslation('object', 'is_object', $entitySource, $entityTranslation, 'en-US');
    }
    
    
    /**
     * Tests all datatype against generic translation
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
    public function testOnSpecificTranslation($type, $typeCheckFunction, $value, $translatedValue, $language = 'en-US'){
        $object = new \CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Specific();
        $object->$type = $value;
        $object->setLocaleForTranslation($language)->$type = $translatedValue;
        
        if(is_callable($typeCheckFunction)){
            $this->assertTrue(call_user_func($typeCheckFunction, $object->setLocaleForTranslation($language)->$type));
        }
		$this->assertEquals($object->setLocaleForTranslation($language)->$type, $translatedValue);
		$this->assertNotEquals($object->setLocaleForTranslation($language)->$type, $object->setDefaultLocaleForTranslation()->$type);
    }
    
    
    /**
     * Tests object datatype against generic translation
     * 
	 * @test
     */
    public function testObjectOnSpecificTranslation(){
        $entitySource = new Entity('dummy1');
        $entityTranslation = new Entity('dummy2');
		$this->entityRepository->add($entitySource);
		$this->entityRepository->add($entityTranslation);
        
        $this->testOnSpecificTranslation('object', 'is_object', $entitySource, $entityTranslation, 'en-US');
    }
    
    /**
     * Check if object is an instance of DateTime
     * 
     * @param \DateTime $object
     * 
     * @return boolean
     */
    public function isDateTime($object){
        return is_object($object) && $object instanceof \DateTime;
    }
}
