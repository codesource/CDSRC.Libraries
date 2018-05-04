<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model;

use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation;
use CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface;
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
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $stringField;
    
    /**
     * @var boolean
     * @ORM\Column(nullable=true)
     */
    protected $booleanField;
    
    /**
     * @var integer
     * @ORM\Column(nullable=true)
     */
    protected $integerField;
    
    /**
     * @var float
     * @ORM\Column(nullable=true)
     */
    protected $floatField;
    
    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $dateField;
    
    /**
     * @var array
     * @ORM\Column(nullable=true)
     */
    protected $arrayField;
    
    /**
     * @var \CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Entity
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     */
    protected $objectField;
}
