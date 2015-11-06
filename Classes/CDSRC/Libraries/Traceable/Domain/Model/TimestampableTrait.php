<?php

namespace CDSRC\Libraries\Traceable\Domain\Model;

/*******************************************************************************
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ******************************************************************************/

use CDSRC\Libraries\Traceable\Annotations as CDSRC;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Track creations and updates datetime
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
trait TimestampableTrait
{

    /**
     * Datetime of creation
     *
     * @var \DateTime
     * @CDSRC\Traceable(on="create", value="now")
     * @ORM\Column(nullable=true)
     */
    protected $createdAt = null;

    /**
     * Datetime of last update
     *
     * @var \DateTime
     * @CDSRC\Traceable(on="update", value="now")
     * @ORM\Column(nullable=true)
     */
    protected $updatedAt = null;

    /**
     * Get datetime of creation
     *
     * @return \DateTime|NULL
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get datetime of last update
     *
     * @return \DateTime|NULL
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
