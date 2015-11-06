<?php

namespace CDSRC\Libraries\SoftDeletable\Filters;

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
use CDSRC\Libraries\SoftDeletable\Exceptions\PropertyNotFoundException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Core\Bootstrap;

/**
 * ORM query filter to get only active entities
 *
 * @Flow\Proxy(value=false)
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class MarkedAsDeletedFilter extends SQLFilter
{

    /**
     * Store column data for classes
     *
     * @var array
     */
    protected static $data = array();

    /**
     * Disabled class name for filter
     *
     * @var array
     */
    protected static $disabled = array();

    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $className = $targetEntity->getName();
        if (isset(self::$disabled[$className])) {
            return '';
        } elseif (array_key_exists($targetEntity->rootEntityName, self::$disabled)) {
            return '';
        }
        $dataKey = $className . '|' . $targetTableAlias;
        if (!isset(self::$data[$dataKey])) {
            $annotation = $this->getReflectionService()->getClassAnnotation($className, SoftDeletable::class);
            if ($annotation !== null) {
                $existingProperties = $this->getReflectionService()->getClassPropertyNames($className);
                if (!in_array($annotation->deleteProperty, $existingProperties)) {
                    throw new PropertyNotFoundException("Property '" . $annotation->deleteProperty . "' not found for '" . $className . "'", 1439207432);
                }
                $conn = $this->getEntityManager()->getConnection();
                $platform = $conn->getDatabasePlatform();

                $column = $targetEntity->getQuotedColumnName($annotation->deleteProperty, $platform);
                $addCondSql = $platform->getIsNullExpression($targetTableAlias . '.' . $column);
                if ($annotation->timeAware) {
                    $addCondSql .= ' OR ' . $targetTableAlias . '.' . $column . ' > ' . $conn->quote(date('Y-m-d H:i:s'));
                }
                self::$data[$dataKey] = $addCondSql;
            } else {
                self::$data[$dataKey] = false;
            }
        }

        return self::$data[$dataKey] ? self::$data[$dataKey] : '';
    }

    /**
     * Get reflection service from bootstrap
     *
     * @return \TYPO3\Flow\Reflection\ReflectionService
     */
    protected function getReflectionService()
    {
        if ($this->reflectionService === null) {
            $this->reflectionService = Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService');
        }

        return $this->reflectionService;
    }

    /**
     * Get entityManager from parent
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        if ($this->entityManager === null) {
            $reflexion = new \ReflectionProperty('Doctrine\ORM\Query\Filter\SQLFilter', 'em');
            $reflexion->setAccessible(true);
            $this->entityManager = $reflexion->getValue($this);
        }

        return $this->entityManager;
    }

}
