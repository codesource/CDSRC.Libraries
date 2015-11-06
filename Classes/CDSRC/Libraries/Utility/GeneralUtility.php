<?php

namespace CDSRC\Libraries\Utility;

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
    protected static $traitsByClassName = array();

    /**
     * Cache for property annotation
     *
     * @var array
     */
    protected static $propertiesAnnotations = array();

    /**
     * Check if object use trait
     *
     * @param mixed $object
     * @param string $trait
     *
     * @return boolean
     */
    public static function useTrait($object, $trait)
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
    public static function class_uses_recursive($class)
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
     */
    public static function getPropertyAnnotation($className, $property, $prefix = 'CDSRC\\')
    {
        $_className = (string)$className;
        $_property = (string)$property;
        if (!isset(self::$propertiesAnnotations[$_className][$_property])) {
            if (class_exists($className) && property_exists($className, $_property)) {
                $reflectionProperty = new \ReflectionProperty($_className, $_property);
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
