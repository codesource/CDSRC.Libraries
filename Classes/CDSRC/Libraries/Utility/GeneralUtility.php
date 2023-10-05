<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Utility;

use ReflectionException;
use ReflectionProperty;

/**
 * General utilities
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class GeneralUtility
{

    /**
     * Cache trait calling
     *
     * @var array
     */
    protected static array $traitsByClassName = [];

    /**
     * Cache for property annotation
     *
     * @var array
     */
    protected static array $propertiesAnnotations = [];

    /**
     * Check if object use trait
     *
     * @param mixed $object
     * @param string $trait
     *
     * @return boolean
     */
    public static function useTrait(mixed $object, string $trait): bool
    {
        if (is_object($object)) {
            $className = get_class($object);
        } elseif (is_string($object) && class_exists($object)) {
            $className = $object;
        } else {
            return false;
        }
        if (!isset(self::$traitsByClassName[$className])) {
            self::$traitsByClassName[$className] = self::class_uses_recursive($object);
        }

        return in_array($trait, self::$traitsByClassName[$className]);
    }

    /**
     * Get all traits used by class
     *
     * @param $class
     *
     * @return array
     */
    public static function class_uses_recursive($class): array
    {
        $allTraits = array();
        $traits = class_uses($class);
        if (is_array($traits)) {
            foreach ($traits as $trait) {
                $allTraits[] = $trait;
                $allTraits = array_merge($allTraits, self::class_uses_recursive($trait));
            }
        }
        $parents = class_parents($class);
        if (is_array($parents)) {
            foreach ($parents as $parent) {
                $allTraits = array_merge($allTraits, self::class_uses_recursive($parent));
            }
        }

        return array_unique($allTraits);
    }

    /**
     * Return annotations starting with prefix
     *
     * @param string $className
     * @param string $property
     * @param string $prefix
     *
     * @return array
     *
     * @throws ReflectionException
     */
    public static function getPropertyAnnotation(string $className, string $property, string $prefix = 'CDSRC\\'): array
    {
        $_className = $className;
        $_property = $property;
        if (!isset(self::$propertiesAnnotations[$_className][$_property])) {
            if (class_exists($className) && property_exists($className, $_property)) {
                $reflectionProperty = new ReflectionProperty($_className, $_property);
                $annotations = array();
                preg_match_all('/@(' . $prefix . '(.*?))\n/s', $reflectionProperty->getDocComment(), $annotations);
                self::$propertiesAnnotations[$_className][$_property] = $annotations[1];
            } else {
                self::$propertiesAnnotations[$_className][$_property] = array();
            }
        }

        return self::$propertiesAnnotations[$_className][$_property];
    }

}
