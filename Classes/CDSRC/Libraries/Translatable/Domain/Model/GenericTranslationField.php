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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Property\Exception\InvalidDataTypeException;
use TYPO3\Flow\Property\Exception\InvalidPropertyException;

/**
 * @Flow\Entity
 * @ORM\Table(name="cdsrc_libraries_trsl_generictranslationfield")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class GenericTranslationField
{

    /**
     * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
     * @Flow\Inject
     * @Flow\Transient
     */
    protected $persistenceManager;

    /**
     * Parent translation
     *
     * @var GenericTranslation
     * @ORM\ManyToOne(inversedBy="fields")
     */
    protected $translation;

    /**
     * Property's name
     *
     * @var string
     */
    protected $property;

    /**
     * Generic boolean
     *
     * @var boolean
     * @ORM\Column(nullable=true)
     */
    protected $vBoolean;

    /**
     * Generic integer
     *
     * @var integer
     * @ORM\Column(nullable=true)
     */
    protected $vInteger;

    /**
     * Generic float
     * Mapped as double
     *
     * @var float
     * @ORM\Column(nullable=true)
     */
    protected $vFloat;

    /**
     * Generic string
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $vString;

    /**
     * Generic string
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $vText;

    /**
     * Generic datetime
     *
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $vDatetime;

    /**
     * Generic array
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $vArray;

    /**
     * Generic array
     *
     * @var array
     * @Flow\Transient
     */
    protected $vArrayUnserialized;


    /**
     * Generic reference to a translatable object
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $objectReference;

    /**
     * Generic class name of the translatable object
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $objectClassName;

    /**
     * Available value type
     *
     * @var array
     * @Flow\Transient
     */
    protected $types = array('vBoolean', 'vInteger', 'vFloat', 'vString', 'vText', 'vDatetime', 'vArray', 'objectReference', 'objectClassName');

    /**
     * Constructor
     *
     * @param \CDSRC\Libraries\Translatable\Domain\Model\GenericTranslation $translation
     * @param string $property
     *
     * @throws \TYPO3\Flow\Property\Exception\InvalidPropertyException
     */
    public function __construct(GenericTranslation $translation, $property)
    {
        if (!is_string($property) || !preg_match('/^[a-z][a-z0-9_]+$/i', $property)) {
            throw new InvalidPropertyException('"' . $property . '" is not a valid property\'s name', 1428263235);
        }
        $this->translation = $translation;
        $this->property = (string)$property;
    }

    /**
     * Return the field's property
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Return value issues from specific type
     *
     * @return mixed
     */
    public function getValue()
    {
        foreach ($this->types as $type) {
            if ($type !== 'objectClassName' && $this->$type !== null) {
                if ($type === 'vArray') {
                    if (!is_array($this->vArrayUnserialized) && is_string($this->vArray) && strlen($this->vArray) > 0) {
                        $this->vArrayUnserialized = unserialize($this->vArray);
                    }

                    return $this->vArrayUnserialized;
                } elseif ($type === 'objectReference') {
                    $object = $this->persistenceManager->getObjectByIdentifier($this->objectReference, $this->objectClassName);
                    if ($object !== null) {
                        return $object;
                    }
                } else {
                    return $this->$type;
                }
            }
        }

        return null;
    }

    /**
     * Set value
     *
     * @param mixed $value
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\GenericTranslationField
     * @throws InvalidDataTypeException
     */
    public function setValue($value)
    {
        $OK = false;
        if (is_bool($value)) {
            $this->vBoolean = $value;
            $OK = true;
        } elseif (is_integer($value)) {
            $this->vInteger = $value;
            $OK = true;
        } elseif (is_float($value)) {
            $this->vFloat = $value;
            $OK = true;
        } elseif (is_string($value)) {
            if (strlen($value) > 255) {
                $this->vText = $value;
            } else {
                $this->vString = $value;
            }
            $OK = true;
        } elseif (is_array($value)) {
            $this->vArrayUnserialized = $value;
            $serialized = $this->serializeArray($value);
            if ($serialized === false) {
                $this->vArrayUnserialized = null;
                $OK = false;
            } else {
                $this->vArray = $serialized;
                $OK = true;
            }
        } elseif (is_object($value)) {
            if ($value instanceof \DateTime) {
                $this->vDatetime = $value;
                $OK = true;
            } elseif ($value instanceof TranslatableInterface) {
                $this->objectReference = $this->persistenceManager->getIdentifierByObject($value);
                if ($this->objectReference !== null && strlen($this->objectReference) > 0) {
                    $this->objectClassName = get_class($value);
                    $OK = true;
                }
            }
        }
        if (!$OK) {
            throw new InvalidDataTypeException('Given value can\'t be translated by generic translation.', 1428269963);
        }

        return $this;
    }

    /**
     * Serialize an array
     *
     * @param array $array
     * @param int $depth
     *
     * @return FALSE|string if error appends
     */
    protected function serializeArray(array &$array, $depth = 0)
    {
        if ($depth < 50) {
            foreach ($array as &$val) {
                if (is_scalar($val)) {
                    continue;
                } elseif (is_array($val)) {
                    $serialized = $this->serializeArray($val, $depth + 1);
                    if ($serialized === false) {
                        return false;
                    } else {
                        $val = 'ARR->|' . $serialized;
                    }
                } elseif (is_object($val)) {
                    if ($val instanceof \DateTime) {
                        $val = 'DAT->|' . serialize($val);
                    } else {
                        $className = get_class($val);
                        if ($val instanceof TranslatableInterface) {
                            $ref = $this->persistenceManager->getIdentifierByObject($val);
                            if ($ref !== null && strlen($ref) > 0) {
                                $val = 'OBJ->|' . $className . '|' . $ref;
                            } else {
                                return false;
                            }
                        }
                    }
                } else {
                    return false;
                }
            }

            return serialize($array);
        }

        return false;
    }

    /**
     * Reset all values
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\GenericTranslationField
     */
    protected function resetValues()
    {
        foreach ($this->types as $type) {
            $this->$type = null;
        }

        return $this;
    }

    /**
     * Unserialize an array
     *
     * @param string $string
     *
     * @return array|FALSE if error appends
     */
    protected function unserializeArray($string)
    {
        $array = unserialize($string);
        if (is_array($array)) {
            foreach ($array as &$val) {
                if (is_string($val)) {
                    switch (substr($val, 0, 6)) {
                        case 'ARR->|':
                            $val = $this->unserializeArray(substr($val, 6));
                            if ($val === false) {
                                return false;
                            }
                            break;
                        case 'DAT->|':
                            $val = unserialize(substr($val, 6));
                            break;
                        case 'OBJ->|':
                            list($className, $reference) = explode('|', substr($val, 6));
                            if (strlen($className) > 0 && strlen($reference) > 0) {
                                $val = $this->persistenceManager->getObjectByIdentifier($reference, $className);
                                if ($val === false) {
                                    return false;
                                }
                            }
                            break;
                    }
                }
            }

            return $array;
        }

        return false;
    }
}
