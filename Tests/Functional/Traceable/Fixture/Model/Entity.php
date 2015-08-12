<?php

namespace CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model;

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
use CDSRC\Libraries\Traceable\Domain\Model\IpTraceableTrait;
use CDSRC\Libraries\Traceable\Domain\Model\TimestampableTrait;

/**
 * A dummy entity
 *
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Entity {
    use IpTraceableTrait, TimestampableTrait;

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