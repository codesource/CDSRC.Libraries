<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\SoftDeletable\Domain\Model;

use CDSRC\Libraries\SoftDeletable\Annotations as CDSRC;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;

/**
 * SoftDeletable trait
 * NOTICE: Doctrine must be patched with https://github.com/doctrine/annotations/pull/58
 *
 * @CDSRC\SoftDeletable(deleteProperty="deletedAt", hardDeleteProperty="forceDelete")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
trait SoftDeletableTrait
{

    /**
     * Store when the entity has been deleted
     *
     * @var DateTime|null
     * @ORM\Column(nullable=true)
     */
    protected ?DateTime $deletedAt = null;

    /**
     * Who has deleted this entity
     *
     * @var Account|null
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     */
    protected ?Account $deletedBy = null;

    /**
     * Force entity to be hard delete
     *
     * @var bool
     * @Flow\Transient
     */
    protected bool $forceDelete = false;

    /**
     * Returns deletedAt.
     *
     * @return DateTime
     */
    public function getDeletedAt(): DateTime
    {
        return $this->deletedAt;
    }

    /**
     * Sets deletedAt.
     *
     * @param DateTime|null $deletedAt
     *
     * @return SoftDeletableTrait
     */
    public function setDeletedAt(?DateTime $deletedAt = null): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Returns deletedAt.
     *
     * @return Account|null
     */
    public function getDeletedBy(): ?Account
    {
        return $this->deletedBy;
    }

    /**
     * Sets deletedAt.
     *
     * @param Account|null $deletedBy
     *
     * @return SoftDeletableTrait
     */
    public function setDeletedBy(?Account $deletedBy = null): static
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    /**
     * Returns hard delete status
     *
     * @return bool
     */
    public function getForceDelete(): bool
    {
        return $this->forceDelete;
    }

    /**
     * Force/UnForce hard delete
     *
     * @param bool $force
     *
     * @return SoftDeletableTrait
     */
    public function forceHardDelete(bool $force = true): static
    {
        $this->forceDelete = $force;

        return $this;
    }

    /**
     * Is this entity deleted?
     *
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

}
