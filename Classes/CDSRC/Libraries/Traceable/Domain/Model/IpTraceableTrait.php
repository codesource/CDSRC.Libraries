<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Traceable\Domain\Model;

use CDSRC\Libraries\Traceable\Annotations as CDSRC;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trace IP for creations and updates
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
trait IpTraceableTrait
{

    /**
     * IP address on creation
     *
     * @var string|null
     * @CDSRC\Traceable(on="create", value="\CDSRC\Libraries\Traceable\Utility\GeneralUtility::getRemoteAddress()")
     * @ORM\Column(nullable=true)
     */
    protected ?string $createdFromIp = null;

    /**
     * IP address on last update
     *
     * @var string|null
     * @CDSRC\Traceable(on="update", value="\CDSRC\Libraries\Traceable\Utility\GeneralUtility::getRemoteAddress()")
     * @ORM\Column(nullable=true)
     */
    protected ?string $updatedFromIp = null;

    /**
     * Get IP address of creation
     *
     * @return string|null
     */
    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    /**
     * Get IP address of last update
     *
     * @return string|null
     */
    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }
}
