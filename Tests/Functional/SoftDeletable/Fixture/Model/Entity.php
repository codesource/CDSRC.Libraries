<?php

namespace CDSRC\Libraries\Tests\Functional\SoftDeletable\Fixture\Model;

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

use CDSRC\Libraries\SoftDeletable\Annotations as CDSRC;
use CDSRC\Libraries\SoftDeletable\Domain\Model\SoftDeletableTrait as SoftDeletable;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A dummy entity
 *
 * @Flow\Entity
 * @CDSRC\SoftDeletable(deleteProperty="deletedAt", hardDeleteProperty="forceDelete", timeAware=true)
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Entity
{
    use SoftDeletable;

    /**
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $type;

    public function __construct($type = '')
    {
        $this->type = $type;
    }

    public function someMethod()
    {

    }
}
