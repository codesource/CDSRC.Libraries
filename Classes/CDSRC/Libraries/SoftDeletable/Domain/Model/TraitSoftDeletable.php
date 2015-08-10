<?php

namespace CDSRC\Libraries\SoftDeletable\Domain\Model;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use CDSRC\Libraries\SoftDeletable\Annotations as CDSRC;

/**
 * 
 * @CDSRC\SoftDeletable(deleteProperty="deletedAt", hardDeleteProperty="forceDelete")
 */
trait TraitSoftDeletable {

    /**
     * Store when the entity has been deleted
     * 
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $deletedAt = NULL;

    /**
     * Who has deleted this entity
     * 
     * @var \TYPO3\Flow\Security\Account
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     */
    protected $deletedBy = NULL;
    
    /**
     * Force entity to be hard delete
     * 
     * @var boolean
     * @Flow\Transient
     */
    protected $forceDelete = FALSE;

    /**
     * Returns deletedAt.
     *
     * @return \DateTime
     */
    public function getDeletedAt() {
        return $this->deletedAt;
    }

    /**
     * Returns deletedAt.
     *
     * @return \TYPO3\Flow\Security\Account
     */
    public function getDeletedBy() {
        return $this->deletedBy;
    }
    
    /**
     * Returns hard delete status
     * 
     * @return boolean
     */
    public function getForceDelete(){
        return $this->forceDelete;
    }

    /**
     * Sets deletedAt.
     *
     * @param \Datetime $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(\DateTime $deletedAt = NULL) {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Sets deletedAt.
     *
     * @param \TYPO3\Flow\Security\Account $deletedBy
     *
     * @return $this
     */
    public function setDeletedBy(\TYPO3\Flow\Security\Account $deletedBy = NULL) {
        $this->deletedBy = $deletedBy;
        return $this;
    }
    
    /**
     * Force/Unforce hard delete
     * 
     * @param boolean $force
     * @return $this
     */
    public function forceHardDelete($force = TRUE){
        $this->forceDelete = $force;
        return $this;
    }

    /**
     * Is this entity deleted?
     * 
     * @return boolean
     */
    public function isDeleted() {
        return $this->deletedAt !== NULL;
    }

}
