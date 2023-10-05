<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model;

use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation;
use CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A dummy class to translate specific entity
 *
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class SpecificTranslation extends AbstractTranslation implements TranslationInterface {

    /**
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    protected ?string $stringField = null;

    /**
     * @var bool|null
     * @ORM\Column(nullable=true)
     */
    protected ?bool $booleanField = null;

    /**
     * @var int|null
     * @ORM\Column(nullable=true)
     */
    protected ?int $integerField = null;

    /**
     * @var float|null
     * @ORM\Column(nullable=true)
     */
    protected ?float $floatField = null;

    /**
     * @var DateTime|null
     * @ORM\Column(nullable=true)
     */
    protected ?DateTime $dateField = null;

    /**
     * @var array|null
     * @ORM\Column(nullable=true)
     */
    protected ?array $arrayField = null;

    /**
     * @var Entity|null
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     */
    protected ?Entity $objectField = null;
}
