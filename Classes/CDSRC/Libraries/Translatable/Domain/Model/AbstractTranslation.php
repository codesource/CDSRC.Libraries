<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Model;

use CDSRC\Libraries\Translatable\Annotations as CDSRC;
use CDSRC\Libraries\Translatable\Annotations\Locked;
use CDSRC\Libraries\Translatable\Annotations\Translatable;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\I18n\Locale;
use Neos\Flow\ObjectManagement\Exception\InvalidObjectException;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Property\Exception\InvalidPropertyException;
use Neos\Flow\Reflection\Exception\InvalidClassException;
use Neos\Flow\Reflection\ReflectionService;

/**
 * Abstract class for translation entities
 *
 * @Flow\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\Table(name="cdsrc_libraries_trsl_abstracttranslation")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractTranslation implements TranslationInterface
{

    /**
     * Store reflexion for translatable properties
     *
     * @var array
     * @Flow\Transient
     * @CDSRC\Locked
     */
    static protected array $propertiesCheckCache = array();

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     * @Flow\Transient
     * @CDSRC\Locked
     */
    protected PersistenceManagerInterface $persistenceManager;

    /**
     * @var ReflectionService
     * @Flow\Inject
     * @Flow\Transient
     * @CDSRC\Locked
     */
    protected ReflectionService $reflectionService;

    /**
     * Class name of parent
     *
     * @var string|null
     * @Flow\Transient
     * @CDSRC\Locked
     */
    protected ?string $parentClassName = null;

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     * @CDSRC\Locked
     */
    protected string $i18nLocale = '';


    /**
     * @var Locale|null
     * @Flow\Transient
     * @CDSRC\Locked
     */
    protected ?Locale $i18nLocaleObject = null;

    /**
     * @var AbstractTranslatable|null
     * @ORM\ManyToOne(inversedBy="translations", cascade={})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Flow\Validate(type="NotEmpty")
     * @CDSRC\Locked
     */
    protected ?AbstractTranslatable $i18nParent = null;

    /**
     * Constructor
     *
     * @param Locale|string $i18nLocale
     * @param string|null $parentClassName
     *
     * @throws InvalidLocaleIdentifierException
     */
    public function __construct(Locale|string $i18nLocale, ?string $parentClassName = null)
    {
        $this->setI18nLocale($i18nLocale);
        $this->parentClassName = $parentClassName;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidLocaleIdentifierException
     */
    public function getI18nLocale(): Locale
    {
        if ($this->i18nLocaleObject === null && strlen($this->i18nLocale) > 0) {
            $this->i18nLocaleObject = new Locale($this->i18nLocale);
        }

        return $this->i18nLocaleObject;
    }

    /**
     * @param Locale|string $locale
     *
     * @return $this
     *
     * @throws InvalidLocaleIdentifierException
     */
    public function setI18nLocale(Locale|string $locale): static
    {
        if (is_string($locale) && strlen($locale) > 0) {
            $locale = new Locale($locale);
        }
        if ($locale instanceof Locale) {
            $this->i18nLocaleObject = $locale;
            $this->i18nLocale = (string)$this->i18nLocaleObject;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getI18nParent(): TranslatableInterface
    {
        return $this->i18nParent;
    }

    /**
     * {@inheritdoc}
     */
    public function setI18nParent(TranslatableInterface $parent, $bidirectional = true): static
    {
        $this->i18nParent = $parent;
        $this->getParentClassName();

        if ($bidirectional) {
            $this->i18nParent->addTranslation($this);
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
     *
     * @throws InvalidClassException
     * @throws InvalidPropertyException
     */
    public function __call(string $method, array $arguments)
    {
        if (preg_match('/^(get|is|has)([a-z0-9A-Z_]+)$/', $method, $match)) {
            $property = lcfirst($match[2]);
            $annotation = $this->reflectionService->getPropertyAnnotation(get_class($this), $property, 'CDSRC\\Libraries\\Translatable\\Annotations\\Locked');
            if (!$annotation instanceof Locked) {
                return $this->get($property);
            }
        }
        if (preg_match('/^set([a-z0-9A-Z_]+)$/', $method, $match)) {
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
     *
     * @return mixed
     *
     * @throws InvalidClassException
     * @throws InvalidPropertyException
     */
    protected function get(string $property): mixed
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
     *
     * @throws InvalidClassException
     * @throws InvalidPropertyException
     */
    protected function sanitizeProperty(string $property): string
    {
        $_property = lcfirst($property);
        $this->getParentClassName();

        if (strlen($this->parentClassName) <= 0) {
            throw new InvalidClassException('Parent class name has not been set.', 1428243279);
        }

        if (isset(self::$propertiesCheckCache[$this->parentClassName]) && in_array($_property, self::$propertiesCheckCache[$this->parentClassName])) {
            return $_property;
        }

        if (in_array($_property, call_user_func($this->parentClassName . '::getTranslatableFields'))) {
            self::$propertiesCheckCache[$this->parentClassName][] = $_property;

            return $_property;
        }

        if (!property_exists($this->parentClassName, $_property)) {
            throw new InvalidPropertyException($_property . ' do not exists or is not present in "translatableFields" in ' . $this->parentClassName, 1428243278);
        }

        $annotation = $this->reflectionService->getPropertyAnnotation($this->parentClassName, $_property, 'CDSRC\\Libraries\\Translatable\\Annotations\\Translatable');
        if (!$annotation instanceof Translatable) {
            throw new InvalidPropertyException($_property . ' is not translatable.', 1428243280);
        }

        if (!$this->propertyExists($_property)) {
            throw new InvalidPropertyException($_property . ' does not exists in ' . get_class($this), 1428243279);
        }
        self::$propertiesCheckCache[$this->parentClassName][] = $_property;

        return $_property;
    }

    /**
     * Check if current object can handle property
     *
     * @param string $property
     *
     * @return boolean
     */
    protected function propertyExists(string $property): bool
    {
        return property_exists($this, $property);
    }

    /**
     * Property setter
     *
     * @param string $property
     * @param mixed $value
     *
     * @return AbstractTranslatable
     *
     * @throws InvalidClassException
     * @throws InvalidPropertyException
     */
    protected function set(string $property, mixed $value): AbstractTranslatable
    {
        $_property = $this->sanitizeProperty($property);
        $this->$_property = $value;

        return $this->i18nParent;
    }

    /**
     * @return string|null
     */
    protected function getParentClassName(): ?string
    {
        if ($this->parentClassName === null || strlen($this->parentClassName) === 0) {
            if (isset($this->i18nParent)) {
                $this->parentClassName = get_class($this->i18nParent);
            } else {
                $parentClassName = substr(get_called_class(), 0, -11);
                if (class_exists($parentClassName)) {
                    $this->parentClassName = $parentClassName;
                }
            }
        }

        return $this->parentClassName;
    }

    /**
     * @return array
     */
    protected function getParentTranslatableFields(): array
    {
        $parentClassName = $this->getParentClassName();
        if ($parentClassName) {
            return call_user_func($this->parentClassName . '::getTranslatableFields');
        }

        return [];
    }


    /**
     * @param AbstractTranslation $translation
     *
     * @return AbstractTranslation
     *
     * @throws InvalidClassException
     * @throws InvalidObjectException
     * @throws InvalidPropertyException
     */
    public function mergeWithTranslation(AbstractTranslation $translation): static
    {
        $currentClassName = get_class($this);
        $otherClassName = get_class($translation);
        if ($currentClassName !== $otherClassName) {
            throw new InvalidObjectException(
                sprintf('Transactions are not same class "%s" <> "%s"', $currentClassName, $otherClassName),
                1539266857
            );
        }

        foreach ($this->getParentTranslatableFields() as $field) {
            $this->set($field, $translation->get($field));
        }

        return $this;
    }
}
