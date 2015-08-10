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
    protected $preventNesting = FALSE;
    
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
        return $this->wrapCall('__call', array($method, $arguments));
    }

    /**
     * {@inheritdoc}
     */
    public function countAll() {
        return $this->wrapCall('countAll', array());
    }

    /**
     * {@inheritdoc}
     */
    public function findAll() {
        return $this->wrapCall('findAll', array());
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifier($identifier) {
        return $this->wrapCall('findByIdentifier', array($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery() {
        return $this->wrapCall('createQuery', array());
    }
    
    /**
     * Wrap call to enable or disable delete check
     * 
     * @param string $method
     * @param array $arguments
     * 
     * @return mixed
     */
    protected function wrapCall($method, array $arguments){
        if($this->preventNesting){
            $result = call_user_func_array('parent::'.$method, $arguments);
        }else{
            $this->preventNesting = TRUE;
            $filterMethod = $this->enableDeleted ? 'disableForEntity' : 'enableForEntity';
            $this->enableDeleted = $this->previousEnableDeleted;
            $result = Filter::$filterMethod($this->getEntityClassName(), array($this, $method), $arguments);
        }
        $this->preventNesting = FALSE;
        return $result;
    }

}
