<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Traceable\Domain\Model;

use CDSRC\Libraries\Traceable\Annotations as CDSRC;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Security\Account;

/**
 * Trace who created or updated entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
trait UserTraceableTrait {

    /**
     * User that created entity
     *
     * @var Account|null
     * @CDSRC\Traceable(on="create", value="\CDSRC\Libraries\Traceable\Utility\GeneralUtility::getAuthenticatedAccount()", autoCreate=false)
     * @ORM\ManyToOne(targetEntity="\Neos\Flow\Security\Account")
     * @ORM\Column(nullable=true)
     */
    protected ?Account $createdBy = null;

    /**
     * Last user that have update entity
     *
     * @var Account|null
     * @CDSRC\Traceable(on="update", value="\CDSRC\Libraries\Traceable\Utility\GeneralUtility::getAuthenticatedAccount()", autoCreate=false)
     * @ORM\ManyToOne(targetEntity="\Neos\Flow\Security\Account")
     * @ORM\Column(nullable=true)
     */
    protected ?Account $updatedBy = null;

    /**
     * Get who has created entity
     *
     * @return Account|NULL
     */
    public function getCreatedBy(): ?Account
    {
        return $this->createdBy;
    }

    /**
     * Get who has done last update
     *
     * @return Account|NULL
     */
    public function getUpdatedBy(): ?Account
    {
        return $this->updatedBy;
    }
}
