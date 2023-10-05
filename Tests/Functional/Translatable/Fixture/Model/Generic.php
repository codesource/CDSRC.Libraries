<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model;

use CDSRC\Libraries\Translatable\Annotations as CDSRC;
use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslatable;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Locale;

/**
 * A dummy class with generic translation
 *
 * @Flow\Entity
 * @ORM\InheritanceType("JOINED")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Generic extends AbstractTranslatable
{

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected string $notTranslatableField;

    /**
     * @var string
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected string $stringField;

    /**
     * @var bool
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected bool $booleanField;

    /**
     * @var int
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected int $integerField;

    /**
     * @var float
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected float $floatField;

    /**
     * @var DateTime
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected DateTime $dateField;

    /**
     * @var array
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected array $arrayField;

    /**
     * @var Entity
     * @ORM\ManyToOne
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected Entity $objectField;

    /**
     * Generic variable setter
     *
     * @param string $name
     * @param mixed $value
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function __set(string $name, mixed $value)
    {
        $match = array();
        if (preg_match('/^(set)?(string|boolean|integer|float|date|array|object)$/i', $name, $match)) {
            $variable = strtolower($match[2]) . 'Field';
            $this->setPropertyTranslation($variable, $value);
        }
    }


    /**
     * Generic variable getter
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        $match = array();
        if (preg_match('/^(get)?(string|boolean|integer|float|date|array|object)$/i', $name, $match)) {
            $variable = strtolower($match[2]) . 'Field';

            return $this->getPropertyTranslation($variable);
        }

        return null;
    }

    /**
     * @param Locale $locale
     * TODO IMPLEMENT THIS IN ABSTRACT CLASS
     * @return Generic;
     */
    public function setLocaleForTranslation(Locale $locale): static
    {
        return $this;
    }

    /**
     * TODO IMPLEMENT THIS IN ABSTRACT CLASS
     * @return $this;
     */
    public function setDefaultLocaleForTranslation(): static
    {
        return $this;
    }

    /**
     * TODO IMPLEMENT THIS IN ABSTRACT CLASS
     * @param string $property
     *
     * @return string|null
     */
    public function getPropertyTranslation(string $property): ?string
    {
        return null;
    }

    /**
     * @param string $property
     * @param mixed $value
     * TODO IMPLEMENT THIS IN ABSTRACT CLASS
     * @return $this;
     */
    public function setPropertyTranslation(string $property, mixed $value): static
    {
        return $this;
    }
}
