<?php

namespace CDSRC\Libraries\Translatable\Domain\Model;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use CDSRC\Libraries\Translatable\Annotations as CDSRC;
use CDSRC\Libraries\Utility\GeneralUtility;

/**
 * 
 */
abstract class AbstractTranslation {

    /**
     * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
     * @Flow\Inject
     * @Flow\Transient
     */
    protected $persistenceManager;

    /**
     * @var string
     * @Flow\Transient
     */
    const RELATED_TRAIT = 'CDSRC\\Libraries\\Translatable\\Domain\\Model\\TranslatableTrait';

    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     * @Flow\Inject
     * @Flow\Transient
     * @CDSRC\Locked
     */
    protected $reflectionService;

    /**
     * Store reflexion for translatable properties
     * 
     * @var array 
     * @Flow\Transient
     * @CDSRC\Locked
     */
    static protected $propertiesCheckCache = array();

    /**
     * Reference to object identifiant
     * 
     * @var string
     * @CDSRC\Locked
     */
    protected $referenceToObject;

    /**
     * Reference to object class name
     * 
     * @var string
     * @CDSRC\Locked
     */
    protected $classnameOfObject;

    /**
     * Locale of translation
     * 
     * @var string
     * @CDSRC\Locked
     */
    protected $currentLocale;

    /**
     * Constructor
     * 
     * @param string $classname
     * @param string $locale
     */
    public function __construct($classname, $locale) {
        $this->classnameOfObject = (string) $classname;
        $this->currentLocale = (string) $locale;
        if (!GeneralUtility::useTrait($this->classnameOfObject, self::RELATED_TRAIT)) {
            throw new \TYPO3\Flow\Object\Exception\InvalidClassException('Given class ' . $this->classnameOfObject . ' doesn\'t use the trait ' . self::RELATED_TRAIT, 1428262482);
        }
    }
    
    /**
     * Clone object and set the new reference
     * 
     * @param mixed $reference
     * @return CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation
     */
    public function cloneObject($reference){
        $clone = clone $this;
        return $clone->setReference($reference);
    }
    
    /**
     * Set translation reference
     * 
     * @param mixed $reference
     * @return CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation
     */
    public function setReference($reference){
        $this->referenceToObject = is_object($reference) ? $this->persistenceManager->getIdentifierByObject($reference) : (string) $reference;
        return $this;
    }

    /**
     * Property's getter and setter
     * @param type $method
     * @param type $arguments
     */
    public function __call($method, $arguments) {
        $match = NULL;
        if (preg_match('/^get([A-Z][a-z0-9A-Z]*)$/', $method, $match)) {
            $property = lcfirst($match[1]);
            $annotation = $this->reflectionService->getPropertyAnnotation(get_class($this), $property, 'CDSRC\\Libraries\\Translatable\\Annotations\\Locked');
            if (!$annotation instanceof \CDSRC\Libraries\Translatable\Annotations\Locked) {
                return $this->get($property);
            }
        }
        if (preg_match('/^set([A-Z][a-z0-9A-Z]*)$/', $method, $match)) {
            $property = lcfirst($match[1]);
            $annotation = $this->reflectionService->getPropertyAnnotation(get_class($this), $property, 'CDSRC\\Libraries\\Translatable\\Annotations\\Locked');
            if (!$annotation instanceof \CDSRC\Libraries\Translatable\Annotations\Locked) {
                return $this->set($property, $arguments[0]);
            }
        }
    }

    /**
     * Return classname of object
     * 
     * @return string
     */
    public function getClassnameOfObject() {
        return $this->classnameOfObject;
    }

    /**
     * Return current translation locale
     * 
     * @return string
     */
    public function getCurrentLocale() {
        return $this->currentLocale;
    }

    /**
     * Sanitize property to make sure that it's translatable
     * 
     * @param string $property
     * @return string
     * @throws \TYPO3\Flow\Property\Exception\InvalidPropertyException
     */
    protected function sanitizeProperty($property) {
        $_property = lcfirst($property);
        if (isset(self::$propertiesCheckCache[$this->classnameOfObject]) && in_array($_property, self::$propertiesCheckCache[$this->classnameOfObject])) {
            return $_property;
        } else {
            if (!property_exists($this->classnameOfObject, $_property)) {
                throw new \TYPO3\Flow\Property\Exception\InvalidPropertyException($_property . ' do not exists in ' . $this->classnameOfObject, 1428243278);
            }
            $annotation = $this->reflectionService->getPropertyAnnotation($this->classnameOfObject, $_property, 'CDSRC\\Libraries\\Translatable\\Annotations\\Translatable');
            if (!$annotation instanceof \CDSRC\Libraries\Translatable\Annotations\Translatable) {
                throw new \TYPO3\Flow\Property\Exception\InvalidPropertyException($_property . ' is not translatable.', 1428243280);
            }
            if (!$this->propertyExists($_property)) {
                throw new \TYPO3\Flow\Property\Exception\InvalidPropertyException($_property . ' do not exists in ' . get_class($this), 1428243279);
            }
            self::$propertiesCheckCache[$this->classnameOfObject][] = $_property;
            return $_property;
        }
    }

    /**
     * Check if current object can handle property
     * 
     * @param string $property
     * @return boolean
     */
    protected function propertyExists($property) {
        return property_exists($this, $property);
    }

    /**
     * Property getter
     * 
     * @param string $property
     */
    protected function get($property) {
        $_property = $this->sanitizeProperty($property);
        return $this->$_property;
    }

    /**
     * Property setter
     * @param string $property
     * @param mixed $value
     */
    protected function set($property, $value) {
        $_property = $this->sanitizeProperty($property);
        $this->$_property = $value;
    }

}
