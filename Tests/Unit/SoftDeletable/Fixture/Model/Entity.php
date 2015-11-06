<?php
namespace CDSRC\Libraries\Tests\Unit\SoftDeletable\Fixture\Model;
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

use CDSRC\Libraries\SoftDeletable\Domain\Model\SoftDeletableTrait as SoftDeletable;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A dummy entity
 *
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Entity {
    use SoftDeletable;
    
    /**
     *
     * @var string 
     * @ORM\Column(nullable=true)
     */
    protected $type;
    
    public function __construct($type = '') {
        $this->type = $type;
    }
}
