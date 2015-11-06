<?php

namespace CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model;

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
use CDSRC\Libraries\Traceable\Domain\Model\TimestampableTrait as TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A dummy entity to test Timestampable trait
 *
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Timestampable {
    use TimestampableTrait;

    /**
     *
     * @var string 
     * @ORM\Column(nullable=true)
     */
    protected $type;

    /**
     *
     * @var \DateTime
     * @CDSRC\Traceable(on="change", value="now", field="type")
     * @ORM\Column(nullable=true)
     */
    protected $changedAt;

    /**
     *
     * @var \DateTime
     * @CDSRC\Traceable(on="change", value="now", field="type", fieldValues="array('value1', 'value2')")
     * @ORM\Column(nullable=true)
     */
    protected $changedAtIf;

    public function __construct($type = '') {
        $this->type = $type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getChangedAt() {
        return $this->changedAt;
    }

    public function getChangedAtIf() {
        return $this->changedAtIf;
    }
}
