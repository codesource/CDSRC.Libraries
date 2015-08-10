<?php

namespace CDSRC\Libraries\SoftDeletable\Filters;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable;
use CDSRC\Libraries\SoftDeletable\Exceptions\PropertyNotFoundException;
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
     * Store disabled class
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
        if (!isset(self::$datas[$className])) {
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
                self::$datas[$className] = $addCondSql;
            } else {
                self::$datas[$className] = FALSE;
            }
        }
        return self::$datas[$className] ? self::$datas[$className] : '';
    }

    /**
     * Disable filter for given class
     * If callback is given, entity will be disabled only for the call
     * 
     * @param mixed $class
     * @param callable $callback
     * @param array $parameters
     */
    public static function disableForEntity($class, $callback = NULL, array $parameters = array()) {
        $className = is_object($class) ? get_class($class) : $class;
        $result = TRUE;
        if(is_callable($callback)){
            if(isset(self::$disabled[$className])){
                $result = call_user_func_array($callback, $parameters);
            }else{
                self::$disabled[$className] = TRUE;
                $result = call_user_func_array($callback, $parameters);
                unset(self::$disabled[$className]);
            }
        }else{
            self::$disabled[$className] = TRUE;
        }
        return $result;
    }

    /**
     * Enable filter for given class
     * If callback is given, entity will be enabled only for the call
     * 
     * @param mixed $class
     * @param callable $callback
     * @param array $parameters
     * 
     */
    public static function enableForEntity($class, $callback = NULL, array $parameters = array()) {
        $className = is_object($class) ? get_class($class) : $class;
        $result = TRUE;
        if(is_callable($callback)){
            if(isset(self::$disabled[$className])){
                unset(self::$disabled[$className]);
                $result = call_user_func_array($callback, $parameters);
                self::$disabled[$className] = TRUE;
            }else{
                $result = call_user_func_array($callback, $parameters);
            }
        }elseif(isset($this->disabled[$className])){
            unset($this->disabled[$className]);
        }
        return $result;
    }

    /**
     * Get reflection service from bootstrap
     * 
     * @return \TYPO3\Flow\Reflection\ReflectionService
     */
    protected function getReflectionService(){
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
    protected function getEntityManager(){
        if ($this->entityManager === null) {
            $refl = new \ReflectionProperty('Doctrine\ORM\Query\Filter\SQLFilter', 'em');
            $refl->setAccessible(true);
            $this->entityManager = $refl->getValue($this);
        }
        return $this->entityManager;
    }
}
