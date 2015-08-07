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

use CDSRC\Libraries\Translatable\Annotations as CDSRC;
use CDSRC\Libraries\Translatable\Domain\Model\TranslatableTrait as Translatable;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A dummy class with generic translation
 *
 * @Flow\Entity
 * @ORM\InheritanceType("JOINED")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Generic {
    use Translatable;
    
    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $notTranslatableField;
    
    /**
     * @var string
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected $stringField;
    
    /**
     * @var boolean
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected $booleanField;
    
    /**
     * @var integer
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected $integerField;
    
    /**
     * @var float
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected $floatField;
    
    /**
     * @var \DateTime
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected $dateField;
    
    /**
     * @var array
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected $arrayField;
    
    /**
     * @var \CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Entity
     * @ORM\ManyToOne
     * @CDSRC\Translatable
     * @ORM\Column(nullable=true)
     */
    protected $objectField;
    
    /**
     * Generic variable setter
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value){
        $match = array();
        if(preg_match('/^(set)?(string|boolean|integer|float|date|array|object)$/i', $name, $match)){
            $variable = strtolower($match[2]) . 'Field';
            $this->setPropertyTranslation($variable, $value);
        }
    }
    
    
    /**
     * Generic variable getter
     * 
     * @param string $name
     */
    public function __get($name){
        $match = array();
        if(preg_match('/^(get)?(string|boolean|integer|float|date|array|object)$/i', $name, $match)){
            $variable = strtolower($match[2]) . 'Field';
            return $this->getPropertyTranslation($variable);
        }
        return NULL;
    }
}
