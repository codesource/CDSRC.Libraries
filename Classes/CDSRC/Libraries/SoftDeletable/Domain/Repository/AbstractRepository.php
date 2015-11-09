<?php

namespace CDSRC\Libraries\SoftDeletable\Domain\Repository;

/*******************************************************************************
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ******************************************************************************/

use CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * Abstract repository for SoftDeletable entities
 *
 * @Flow\Scope("singleton")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractRepository extends Repository
{

    /**
     *
     * @var boolean
     */
    protected $enableDeleted = false;

    /**
     * @var SoftDeletable|boolean
     */
    protected $deleteAnnotation = null;

    /**
     * @Flow\Inject
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $entityManager;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     *
     * @return $this
     */
    public function allowDeleted()
    {
        $this->enableDeleted = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        if ($this->enableDeleted) {
            $this->entityManager->getFilters()->disable('cdsrc.libraries.softdeletable.filter');
        }
        $result = parent::findAll();
        $this->enableDeleted = false;
        $this->entityManager->getFilters()->enable('cdsrc.libraries.softdeletable.filter');

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        if ($this->enableDeleted) {
            $this->entityManager->getFilters()->disable('cdsrc.libraries.softdeletable.filter');
        }
        $result = parent::countAll();
        $this->enableDeleted = false;
        $this->entityManager->getFilters()->enable('cdsrc.libraries.softdeletable.filter');

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $arguments)
    {
        if ($this->enableDeleted) {
            $this->entityManager->getFilters()->disable('cdsrc.libraries.softdeletable.filter');
        }
        $result = parent::__call($method, $arguments);
        $this->enableDeleted = false;
        $this->entityManager->getFilters()->enable('cdsrc.libraries.softdeletable.filter');

        return $result;
    }

}
