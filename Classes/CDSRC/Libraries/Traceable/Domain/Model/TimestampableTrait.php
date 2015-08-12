<?php
namespace CDSRC\Libraries\Traceable\Domain\Model;

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
use CDSRC\Libraries\Traceable\Annotations as CDSRC;

/**
 * Track creations and updates datetime
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
trait TimestampableTrait {

    /**
     *
     * @var \DateTime
     * @CDSRC\Traceable(on="create", value="now")
     * @ORM\Column(nullable=true)
     */
    protected $createdAt = NULL;

    /**
     *
     * @var \DateTime
     * @CDSRC\Traceable(on="update", value="now")
     * @ORM\Column(nullable=true)
     */
    protected $updatedAt = NULL;
    
    /**
     * Get datetime of creation
     * 
     * @return \DateTime|NULL
     */
    public function getCreatedAt(){
        return $this->createdAt;
    }
    
    /**
     * Get datetime of last update
     * 
     * @return \DateTime|NULL
     */
    public function getUpdatedAt(){
        return $this->updatedAt;
    }
}
