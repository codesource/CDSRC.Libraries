<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Traceable\Domain\Model;

use CDSRC\Libraries\Traceable\Annotations as CDSRC;
use Doctrine\ORM\Mapping as ORM;

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
