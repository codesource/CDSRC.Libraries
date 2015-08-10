<?php
namespace CDSRC\Libraries\SoftDeletable\Annotations;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

/**
 * Marks a class as soft deletable
 *
 * @Annotation
 * @Target("CLASS")
 */
final class SoftDeletable {

	/**
	 * Entity property that will store deleted date
     * 
	 * @var string
	 */
	public $deleteProperty = 'deletedAt';

	/**
	 * Entity property that will allow to hard delete
     * 
	 * @var string
	 */
	public $hardDeleteProperty = '';

	/**
	 * Entity can be deleted in future and still be available now
     * 
	 * @var boolean
	 */
	public $timeAware = FALSE;
    
}
