<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Model;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Property\Exception\InvalidDataTypeException;
use Neos\Flow\Property\Exception\InvalidPropertyException;

/**
 * @Flow\Entity
 * @ORM\Table(name="cdsrc_libraries_trsl_generictranslationfield")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class GenericTranslationField
{

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     * @Flow\Transient
     */
    protected PersistenceManagerInterface $persistenceManager;

    /**
     * Parent translation
     *
     * @var GenericTranslation
     * @ORM\ManyToOne(inversedBy="fields")
     */
    protected GenericTranslation $translation;

    /**
     * Property's name
     *
     * @var string
     */
    protected string $property;

    /**
     * Generic boolean
     *
     * @var bool|null
     * @ORM\Column(nullable=true)
     */
    protected ?bool $vBoolean = null;

    /**
     * Generic integer
     *
     * @var int|null
     * @ORM\Column(nullable=true)
     */
    protected ?int $vInteger = null;

    /**
     * Generic float
     * Mapped as double
     *
     * @var float|null
     * @ORM\Column(nullable=true)
     */
    protected ?float $vFloat = null;

    /**
     * Generic string
     *
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    protected ?string $vString = null;

    /**
     * Generic string
     *
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $vText = null;

    /**
     * Generic datetime
     *
     * @var DateTime|null
     * @ORM\Column(nullable=true)
     */
    protected ?DateTime $vDatetime = null;

    /**
     * Generic array
     *
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $vArray = null;

    /**
     * Generic array
     *
     * @var array|null
     * @Flow\Transient
     */
    protected ?array $vArrayUnserialized = null;


    /**
     * Generic reference to a translatable object
     *
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    protected ?string $objectReference = null;

    /**
     * Generic class name of the translatable object
     *
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    protected ?string $objectClassName = null;

    /**
     * Available value type
     *
     * @var array
     * @Flow\Transient
     */
    protected array $types = ['vBoolean', 'vInteger', 'vFloat', 'vString', 'vText', 'vDatetime', 'vArray', 'objectReference', 'objectClassName'];

    /**
     * Constructor
     *
     * @param GenericTranslation $translation
     * @param string|null $property
     *
     * @throws InvalidPropertyException
     */
    public function __construct(GenericTranslation $translation, ?string $property)
    {
        if (!is_string($property) || !preg_match('/^[a-z][a-z0-9_]+$/i', $property)) {
            throw new InvalidPropertyException('"' . $property . '" is not a valid property\'s name', 1428263235);
        }
        $this->translation = $translation;
        $this->property = $property;
    }

    /**
     * Return the field's property
     *
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * Return value issues from specific type
     *
     * @return mixed
     */
    public function getValue(): mixed
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
     * @return GenericTranslationField
     *
     * @throws InvalidDataTypeException
     */
    public function setValue(mixed $value): GenericTranslationField
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
                // $OK = false; // Not needed because default value
            } else {
                $this->vArray = $serialized;
                $OK = true;
            }
        } elseif (is_object($value)) {
            if ($value instanceof DateTime) {
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
     * @return string|bool
     */
    protected function serializeArray(array &$array, int $depth = 0): bool|string
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
                    if ($val instanceof DateTime) {
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
     * @return GenericTranslationField
     */
    protected function resetValues(): GenericTranslationField
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
     * @return array|bool
     */
    protected function unserializeArray(string $string): bool|array
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
