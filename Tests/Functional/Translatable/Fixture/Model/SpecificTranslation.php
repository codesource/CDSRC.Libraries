<?php
namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model;
/*
 * Copyright (C) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A dummy class with generic translation
 *
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class SpecificTranslation extends \CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation {
    
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
