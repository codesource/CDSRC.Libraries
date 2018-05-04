<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\SoftDeletable\Domain\Model;

use CDSRC\Libraries\SoftDeletable\Annotations as CDSRC;
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
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $deletedAt = null;

    /**
     * Who has deleted this entity
     *
     * @var \Neos\Flow\Security\Account
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     */
    protected $deletedBy = null;

    /**
     * Force entity to be hard delete
     *
     * @var boolean
     * @Flow\Transient
     */
    protected $forceDelete = false;

    /**
     * Returns deletedAt.
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Sets deletedAt.
     *
     * @param \Datetime $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Returns deletedAt.
     *
     * @return \Neos\Flow\Security\Account
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Sets deletedAt.
     *
     * @param \Neos\Flow\Security\Account $deletedBy
     *
     * @return $this
     */
    public function setDeletedBy(Account $deletedBy = null)
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    /**
     * Returns hard delete status
     *
     * @return boolean
     */
    public function getForceDelete()
    {
        return $this->forceDelete;
    }

    /**
     * Force/UnForce hard delete
     *
     * @param boolean $force
     *
     * @return $this
     */
    public function forceHardDelete($force = true)
    {
        $this->forceDelete = $force;

        return $this;
    }

    /**
     * Is this entity deleted?
     *
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->deletedAt !== null;
    }

}
