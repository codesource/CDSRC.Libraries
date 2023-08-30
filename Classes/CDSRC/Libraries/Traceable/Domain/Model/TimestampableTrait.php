<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Traceable\Domain\Model;

use CDSRC\Libraries\Traceable\Annotations as CDSRC;
use DateTime;
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
     * @var DateTime|null
     * @CDSRC\Traceable(on="create", value="now")
     * @ORM\Column(nullable=true)
     */
    protected ?DateTime $createdAt = null;

    /**
     * Datetime of last update
     *
     * @var DateTime|null
     * @CDSRC\Traceable(on="update", value="now")
     * @ORM\Column(nullable=true)
     */
    protected ?DateTime $updatedAt = null;

    /**
     * Get datetime of creation
     *
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * Get datetime of last update
     *
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }
}
