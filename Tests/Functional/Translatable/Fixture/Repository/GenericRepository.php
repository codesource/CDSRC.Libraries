<?php
namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A repository for Generic
 * @TYPO3\Flow\Annotations\Scope("singleton")
 */
class GenericRepository extends \TYPO3\Flow\Persistence\Repository {

	/**
	 * @var string
	 */
	const ENTITY_CLASSNAME = 'CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Generic';

}
