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
     *
     * @var boolean 
     */
    protected $previousEnableDeleted = FALSE;

    /**
     * 
     * @return \AbstractRepository
     */
    public function allowDeleted() {
        $this->previousEnableDeleted = $this->enableDeleted;
        $this->enableDeleted = TRUE;
        return $this;
    }

    /**
     * 
     * @return \AbstractRepository
     */
    public function disallowDeleted() {
        $this->previousEnableDeleted = $this->enableDeleted;
        $this->enableDeleted = FALSE;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $arguments) {
        $filterMethod = $this->enableDeleted ? 'disableForEntity' : 'enableForEntity';
        $this->enableDeleted = $this->previousEnableDeleted;
        return Filter::$filterMethod($this->getEntityClassName(), array($this, '__call'), array($method, $arguments));
    }

    /**
     * {@inheritdoc}
     */
    public function countAll() {
        $filterMethod = $this->enableDeleted ? 'disableForEntity' : 'enableForEntity';
        $this->enableDeleted = $this->previousEnableDeleted;
        return Filter::$filterMethod($this->getEntityClassName(), array($this, 'countAll'));
    }

    /**
     * {@inheritdoc}
     */
    public function findAll() {
        $filterMethod = $this->enableDeleted ? 'disableForEntity' : 'enableForEntity';
        $this->enableDeleted = $this->previousEnableDeleted;
        return Filter::$filterMethod($this->getEntityClassName(), array($this, 'findAll'));
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifier($identifier) {
        $filterMethod = $this->enableDeleted ? 'disableForEntity' : 'enableForEntity';
        $this->enableDeleted = $this->previousEnableDeleted;
        return Filter::$filterMethod($this->getEntityClassName(), array($this, 'findByIdentifier'), array($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery() {
        $filterMethod = $this->enableDeleted ? 'disableForEntity' : 'enableForEntity';
        $this->enableDeleted = $this->previousEnableDeleted;
        return Filter::$filterMethod($this->getEntityClassName(), array($this, 'createQuery'));
    }

}
