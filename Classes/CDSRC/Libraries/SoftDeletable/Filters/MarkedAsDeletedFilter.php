<?php

namespace CDSRC\Libraries\SoftDeletable\Filters;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable;
use CDSRC\Libraries\SoftDeletable\Exceptions\PropertyNotFoundException;
use CDSRC\Libraries\SoftDeletable\Exceptions\InfiniteLoopException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(value=false)
 */
class MarkedAsDeletedFilter extends SQLFilter {

    /**
     * Store column data for classes
     * @var array
     */
    protected static $datas = array();

    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Disabled classname for filter
     * 
     * @var array
     */
    protected static $disabled = array();

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias) {
        $className = $targetEntity->getName();
        if (isset(self::$disabled[$className])) {
            return '';
        } elseif (array_key_exists($targetEntity->rootEntityName, self::$disabled)) {
            return '';
        }
        $dataKey = $className . '|' . $targetTableAlias;
        if (!isset(self::$datas[$dataKey])) {
            $annotation = $this->getReflectionService()->getClassAnnotation($className, SoftDeletable::class);
            if ($annotation !== NULL) {
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
                self::$datas[$dataKey] = $addCondSql;
            } else {
                self::$datas[$dataKey] = FALSE;
            }
        }
        return self::$datas[$dataKey] ? self::$datas[$dataKey] : '';
    }

    /**
     * Get reflection service from bootstrap
     * 
     * @return \TYPO3\Flow\Reflection\ReflectionService
     */
    protected function getReflectionService() {
        if ($this->reflectionService === null) {
            $this->reflectionService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService');
        }
        return $this->reflectionService;
    }

    /**
     * Get entityManager from parent
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager() {
        if ($this->entityManager === null) {
            $refl = new \ReflectionProperty('Doctrine\ORM\Query\Filter\SQLFilter', 'em');
            $refl->setAccessible(true);
            $this->entityManager = $refl->getValue($this);
        }
        return $this->entityManager;
    }

}
