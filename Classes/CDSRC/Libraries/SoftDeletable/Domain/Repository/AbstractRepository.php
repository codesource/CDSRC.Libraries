<?php

namespace CDSRC\Libraries\SoftDeletable\Domain\Repository;

/*
 * Copyright (C) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use TYPO3\Flow\Annotations as Flow;
use CDSRC\Libraries\SoftDeletable\Filters\MarkedAsDeletedFilter as Filter;

/**
 * A repository for Generic
 * @Flow\Scope("singleton")
 */
abstract class AbstractRepository extends \TYPO3\Flow\Persistence\Repository {

    /**
     *
     * @var boolean 
     */
    protected $enableDeleted = FALSE;

    /**
     * @var \CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable|boolean
     */
    protected $deleteAnnotation = NULL;

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
     * Constructor
     * @param \TYPO3\Flow\Object\ObjectManagerInterface $objectManager
     */
    public function __construct(\TYPO3\Flow\Object\ObjectManagerInterface $objectManager) {
        parent::__construct();
        $this->entityManager = $objectManager->get('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * 
     * @return \AbstractRepository
     */
    public function allowDeleted() {
        $this->enableDeleted = TRUE;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll() {
        if ($this->enableDeleted) {
            $this->entityManager->getFilters()->disable('cdsrc.libraries.softdeletable.filter');
        }
        $result = parent::findAll();
        $this->enableDeleted = FALSE;
        $this->entityManager->getFilters()->enable('cdsrc.libraries.softdeletable.filter');
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll() {
        if ($this->enableDeleted) {
            $this->entityManager->getFilters()->disable('cdsrc.libraries.softdeletable.filter');
        }
        $result = parent::countAll();
        $this->enableDeleted = FALSE;
        $this->entityManager->getFilters()->enable('cdsrc.libraries.softdeletable.filter');
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery() {
        $query = parent::createQuery();
        if (!$this->enableDeleted) {
            if ($this->deleteAnnotation === NULL) {
                $this->deleteAnnotation = FALSE;
                $annotation = $this->reflectionService->getClassAnnotation($this->entityClassName, \CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable::class);
                if ($annotation !== NULL) {
                    $existingProperties = $this->reflectionService->getClassPropertyNames($this->entityClassName);
                    if (in_array($annotation->deleteProperty, $existingProperties)) {
                        $this->deleteAnnotation = $annotation;
                    }
                }
            }
            if ($this->deleteAnnotation !== FALSE) {
                if ($this->deleteAnnotation->timeAware) {
                    $query = $query->matching($query->logicalOr(
                                    $query->equals($this->deleteAnnotation->deleteProperty, NULL), $query->lessThanOrEqual($this->deleteAnnotation->deleteProperty, new \DateTime())
                    ));
                } else {
                    $query = $query->matching($query->equals($this->deleteAnnotation->deleteProperty, NULL));
                }
            }
        }
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $arguments) {
        if ($this->enableDeleted) {
            $this->entityManager->getFilters()->disable('cdsrc.libraries.softdeletable.filter');
        }
        $result = parent::__call($method, $arguments);
        $this->enableDeleted = FALSE;
        $this->entityManager->getFilters()->enable('cdsrc.libraries.softdeletable.filter');
        return $result;
    }

}
