<?php

namespace CDSRC\Libraries\Utility;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

/**
 * General utilities
 */
class GeneralUtility {

    /**
     * Cache trait calling
     * @var array
     */
    protected static $traitsByClassName = array();

    /**
     * Cache for property annontation
     * @var array
     */
    protected static $propertiesAnnontations = array();

    /**
     * Check if object use trait
     * 
     * @param mixed $object
     * @param string $trait
     * @return boolean
     */
    public static function useTrait($object, $trait) {
        if (is_object($object)) {
            $className = get_class($object);
        } elseif (is_string($object) && class_exists($object)) {
            $className = $object;
        } else {
            return FALSE;
        }
        if (!isset(self::$traitsByClassName[$className])) {
            self::$traitsByClassName[$className] = self::class_uses_recursive($object);
        }
        return in_array($trait, self::$traitsByClassName[$className]);
    }

    public static function class_uses_recursive($class) {
        $allTraits = array();
        $traits = class_uses($class);
        if (is_array($traits)) {
            foreach ($traits as $trait) {
                $allTraits[] = $trait;
                $allTraits = array_merge($allTraits, self::class_uses_recursive($trait));
            }
        }
        $parents = class_parents($class);
        if(is_array($parents)){
            foreach($parents as $parent){
                $allTraits = array_merge($allTraits, self::class_uses_recursive($parent));
            }
        }
        return array_unique($allTraits);
    }

    /**
     * Return annotations starting with prefix
     * @param string $className
     * @param string $property
     * @return array
     */
    static function getPropertyAnnotation($className, $property, $prefix = 'CDSRC\\') {
        $_className = (string) $className;
        $_property = (string) $property;
        if (!isset(self::$propertiesAnnontations[$_className][$_property])) {
            if (class_exists($className) && property_exists($className, $property)) {
                $reflectionProperty = new \ReflectionProperty($this->classname, $property);
                $annotations = array();
                preg_match_all('/@(' . $prefix . '(.*?))\n/s', $reflectionProperty->getDocComment(), $annotations);
                self::$propertiesAnnontations[$_className][$_property] = $annotations[1];
            } else {
                self::$propertiesAnnontations[$_className][$_property] = array();
            }
        }
        return self::$propertiesAnnontations[$_className][$_property];
    }

}
