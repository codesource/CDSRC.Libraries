<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Model;

use CDSRC\Libraries\Translatable\Annotations as CDSRC;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var Collection<GenericTranslationField>
     * @ORM\OneToMany(mappedBy="translation", cascade={"all"}, orphanRemoval=TRUE)
     * @Flow\Lazy
     * @CDSRC\Locked
     */
    public Collection $fields;

    /**
     * Constructor
     *
     * @param Locale|string $i18nLocale
     * @param string $parentClassName
     *
     * @throws InvalidLocaleIdentifierException
     */
    public function __construct(Locale|string $i18nLocale, string $parentClassName = '')
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
    protected function get(string $property): mixed
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
     * @return AbstractTranslatable
     *
     * @throws InvalidClassException
     * @throws InvalidPropertyException
     * @throws InvalidDataTypeException
     */
    protected function set(string $property, mixed $value): AbstractTranslatable
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
     * @return string|null
     */
    protected function getParentClassName(): ?string
    {
        return $this->parentClassName;
    }
}
