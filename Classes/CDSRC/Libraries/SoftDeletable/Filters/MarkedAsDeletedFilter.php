<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\SoftDeletable\Filters;

use CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable;
use CDSRC\Libraries\SoftDeletable\Exceptions\PropertyNotFoundException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use ReflectionException;

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
     * @var \Neos\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * {@inheritdoc}
     *
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     * @throws DBALException
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

                $column = $platform->quoteIdentifier($annotation->deleteProperty);
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
     * @return \Neos\Flow\Reflection\ReflectionService
     */
    protected function getReflectionService()
    {
        if ($this->reflectionService === null) {
            $this->reflectionService = Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService');
        }

        return $this->reflectionService;
    }

    /**
     * Get entityManager from parent
     *
     * @return \Doctrine\ORM\EntityManager
     *
     * @throws ReflectionException
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
