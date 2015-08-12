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
 * Trace who created or updated entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
trait UserTraceableTrait {

    /**
     *
     * @var \TYPO3\Flow\Security\Account
     * @CDSRC\Traceable(on="create", value="\CDSRC\Libraries\Traceable\Utility\GeneralUtility::getAuthenticatedAccount()", autocreate=false)
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     */
    protected $createdBy = NULL;

    /**
     *
     * @var \TYPO3\Flow\Security\Account
     * @CDSRC\Traceable(on="update", value="\CDSRC\Libraries\Traceable\Utility\GeneralUtility::getAuthenticatedAccount()", autocreate=false)
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     */
    protected $updatedBy = NULL;
    
    /**
     * Get who has created entity
     * 
     * @return \TYPO3\Flow\Security\Account|NULL
     */
    public function getCreatedBy(){
        return $this->createdBy;
    }
    
    /**
     * Get who has done last update
     * 
     * @return \TYPO3\Flow\Security\Account|NULL
     */
    public function getUpdatedBy(){
        return $this->updatedBy;
    }
}
