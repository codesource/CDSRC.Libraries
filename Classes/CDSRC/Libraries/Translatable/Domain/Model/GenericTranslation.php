<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Model;

use CDSRC\Libraries\Translatable\Annotations as CDSRC;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\I18n\Locale;
use Neos\Flow\Property\Exception\InvalidDataTypeException;
use Neos\Flow\Property\Exception\InvalidPropertyException;
use Neos\Flow\Reflection\Exception\InvalidClassException;

/**
 * @Flow\Entity
 * @ORM\Table(name="cdsrc_libraries_trsl_generictranslation")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class GenericTranslation extends AbstractTranslation implements TranslationInterface
{

    /**
     * @var \Doctrine\Common\Collections\Collection<\CDSRC\Libraries\Translatable\Domain\Model\GenericTranslationField>
     * @ORM\OneToMany(mappedBy="translation", cascade={"all"}, orphanRemoval=TRUE)
     * @Flow\Lazy
     * @CDSRC\Locked
     */
    public $fields;

    /**
     * Constructor
     *
     * @param Locale|string $i18nLocale
     *
     * @param string $parentClassName
     * @throws InvalidLocaleIdentifierException
     */
    public function __construct($i18nLocale, $parentClassName = '')
    {
        parent::__construct($i18nLocale, $parentClassName);
        $this->fields = new ArrayCollection();
    }

    /**
     * Property getter
     *
     * @param string $property
     *
     * @return mixed|null
     *
     * @throws InvalidPropertyException
     * @throws InvalidClassException
     */
    protected function get($property)
    {
        $_property = $this->sanitizeProperty($property);
        foreach ($this->fields as $field) {
            if ($field->getProperty() === $_property) {
                return $field->getValue();
            }
        }

        return null;
    }

    /**
     * Property setter
     *
     * @param string $property
     * @param mixed $value
     *
     * @return GenericTranslation
     *
     * @throws InvalidClassException
     * @throws InvalidPropertyException
     * @throws InvalidDataTypeException
     */
    protected function set($property, $value)
    {
        $_property = $this->sanitizeProperty($property);
        $field = null;
        foreach ($this->fields as $f) {
            if ($f->getProperty() === $property) {
                $field = $f;
                break;
            }
        }
        if ($field === null) {
            $field = new GenericTranslationField($this, $_property);
            $this->fields->add($field);
        }
        $field->setValue($value);

        return $this;
    }

    /**
     * Get parent class name
     *
     * @return string
     */
    protected function getParentClassName()
    {
        return $this->parentClassName;
    }
}
