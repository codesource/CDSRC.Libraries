<?php

namespace CDSRC\Libraries\Translatable\Domain\Model;

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

use CDSRC\Libraries\Translatable\Annotations as CDSRC;
use CDSRC\Libraries\Translatable\Annotations\Locked;
use CDSRC\Libraries\Translatable\Annotations\Translatable;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\I18n\Locale;
use TYPO3\Flow\Object\Exception\InvalidClassException;
use TYPO3\Flow\Property\Exception\InvalidPropertyException;

/**
 * Abstract class for translation entities
 *
 * @Flow\Entity
 * @ORM\InheritanceType("JOINED")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractTranslation
{

    /**
     * Store reflexion for translatable properties
     *
     * @var array
     * @Flow\Transient
     * @CDSRC\Locked
     */
    static protected $propertiesCheckCache = array();

    /**
     * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
     * @Flow\Inject
     * @Flow\Transient
     * @CDSRC\Locked
     */
    protected $persistenceManager;

    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     * @Flow\Inject
     * @Flow\Transient
     * @CDSRC\Locked
     */
    protected $reflectionService;

    /**
     * Class name of parent
     *
     * @var string
     * @Flow\Transient
     * @CDSRC\Locked
     */
    protected $parentClassName;

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     * @CDSRC\Locked
     */
    protected $i18nLocale;


    /**
     * @var \TYPO3\Flow\I18n\Locale
     * @Flow\Transient
     * @CDSRC\Locked
     */
    protected $i18nLocaleObject;

    /**
     * @var \CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslatable
     * @ORM\ManyToOne(inversedBy="translations")
     * @Flow\Validate(type="NotEmpty")
     * @CDSRC\Locked
     */
    protected $i18nParent;

    /**
     * Constructor
     *
     * @param \TYPO3\Flow\I18n\Locale|string $i18nLocale
     * @param string $parentClassName
     */
    public function __construct($i18nLocale, $parentClassName = '')
    {
        $this->setI18nLocale($i18nLocale);
        $this->parentClassName = $parentClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function getI18nLocale()
    {
        if ($this->i18nLocaleObject === null && strlen($this->i18nLocale) > 0) {
            $this->i18nLocaleObject = new Locale($this->i18nLocale);
        }

        return $this->i18nLocaleObject;
    }

    /**
     * {@inheritdoc}
     */
    public function setI18nLocale($locale)
    {
        if (is_string($locale) && strlen($locale) > 0) {
            $locale = new Locale($locale);
        }
        if (is_object($locale) && $locale instanceof Locale) {
            $this->i18nLocaleObject = $locale;
            $this->i18nLocale = (string)$this->i18nLocaleObject;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getI18nParent()
    {
        return $this->i18nParent;
    }

    /**
     * {@inheritdoc}
     */
    public function setI18nParent(TranslatableInterface $parent, $bidirectional = true)
    {
        $this->i18nParent = $parent;
        $this->parentClassName = get_class($this->i18nParent);
        if ($bidirectional) {
            $this->i18nParent->addTranslation($this, false);
        }

        return $this;
    }

    /**
     * Property's getter and setter
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed|null
     */
    public function __call($method, $arguments)
    {
        $match = null;
        if (preg_match('/^get([a-z0-9A-Z_]+)$/', $method, $match)) {
            $property = lcfirst($match[1]);
            $annotation = $this->reflectionService->getPropertyAnnotation(get_class($this), $property, 'CDSRC\\Libraries\\Translatable\\Annotations\\Locked');
            if (!$annotation instanceof Locked) {
                return $this->get($property);
            }
        }
        if (preg_match('/^set([a-z0-9A-Z_]*)$/', $method, $match)) {
            $property = lcfirst($match[1]);
            $annotation = $this->reflectionService->getPropertyAnnotation(get_class($this), $property, 'CDSRC\\Libraries\\Translatable\\Annotations\\Locked');
            if (!$annotation instanceof Locked) {
                return $this->set($property, $arguments[0]);
            }
        }
        return null;
    }

    /**
     * Property getter
     *
     * @param string $property
     */
    protected function get($property)
    {
        $_property = $this->sanitizeProperty($property);

        return $this->$_property;
    }

    /**
     * Sanitize property to make sure that it's translatable
     *
     * @param string $property
     *
     * @return string
     * @throws InvalidClassException
     * @throws InvalidPropertyException
     */
    protected function sanitizeProperty($property)
    {
        $_property = lcfirst($property);
        if ($this->parentClassName === null && !empty($this->i18nParent)) {
            $this->parentClassName = get_class($this->i18nParent);
        }
        if (strlen($this->parentClassName) > 0) {
            if (isset(self::$propertiesCheckCache[$this->parentClassName]) && in_array($_property, self::$propertiesCheckCache[$this->parentClassName])) {
                return $_property;
            } else {
                if (!property_exists($this->parentClassName, $_property)) {
                    throw new InvalidPropertyException($_property . ' do not exists in ' . $this->parentClassName, 1428243278);
                }
                $annotation = $this->reflectionService->getPropertyAnnotation($this->parentClassName, $_property, 'CDSRC\\Libraries\\Translatable\\Annotations\\Translatable');
                if (!$annotation instanceof Translatable) {
                    throw new InvalidPropertyException($_property . ' is not translatable.', 1428243280);
                }
                if (!$this->propertyExists($_property)) {
                    throw new InvalidPropertyException($_property . ' do not exists in ' . get_class($this), 1428243279);
                }
                self::$propertiesCheckCache[$this->parentClassName][] = $_property;

                return $_property;
            }
        } else {
            throw new InvalidClassException('Parent class name has not been set.', 1428243279);
        }
    }

    /**
     * Check if current object can handle property
     *
     * @param string $property
     *
     * @return boolean
     */
    protected function propertyExists($property)
    {
        return property_exists($this, $property);
    }

    /**
     * Property setter
     *
     * @param string $property
     * @param mixed $value
     *
     * @return $this
     */
    protected function set($property, $value)
    {
        $_property = $this->sanitizeProperty($property);
        $this->$_property = $value;
        return $this;
    }

}
