<?php

namespace CDSRC\Libraries\Traceable\Events;

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

use CDSRC\Libraries\Traceable\Annotations\Traceable;
use CDSRC\Libraries\Traceable\Exceptions\VarAnnotationNotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use TYPO3\Flow\Annotations as Flow;


/**
 * Database event listener
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class EventListener
{

    /**
     * Static store parsed properties
     *
     * @var array
     */
    protected static $storedProperties = array();

    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * Intercept flush event
     *
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {

        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->onFlushForInsertions($entity, $entityManager, $unitOfWork);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->onFlushForUpdates($entity, $entityManager, $unitOfWork);
        }
    }

    /**
     * Flush for insertions
     *
     * @param object $entity
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Doctrine\ORM\UnitOfWork $unitOfWork
     */
    protected function onFlushForInsertions(&$entity, EntityManager &$entityManager, UnitOfWork &$unitOfWork)
    {
        $className = get_class($entity);
        $this->initializeAnnotationsForEntity($className);
        if (count(self::$storedProperties[$className]['annotations']) > 0) {
            foreach (self::$storedProperties[$className]['annotations'] as $propertyName => &$annotations) {
                foreach ($annotations as $annotation) {
                    if ($annotation->on === 'create') {
                        list($oldValue, $value) = $this->updateEntityPropertyValue($entity, $className, $propertyName, $annotation);
                        $entityManager->persist($entity);
                        $unitOfWork->propertyChanged($entity, $propertyName, $oldValue, $value);
                        $unitOfWork->scheduleExtraUpdate($entity, array(
                            $propertyName => array($oldValue, $value)
                        ));
                        break;
                    }
                }
            }
        }
    }

    /**
     * Initialize needed annotations for entity if needed
     *
     * @param string $className
     */
    protected function initializeAnnotationsForEntity($className)
    {
        if (!isset(self::$storedProperties[$className]['annotations'])) {
            self::$storedProperties[$className]['annotations'] = array();
            $propertyNames = $this->reflectionService->getClassPropertyNames($className);
            foreach ($propertyNames as $propertyName) {
                $annotations = $this->reflectionService->getPropertyAnnotations($className, $propertyName, Traceable::class);
                foreach ($annotations as $annotation) {
                    self::$storedProperties[$className]['annotations'][$propertyName][] = $annotation;
                }
            }
        }
    }

    /**
     * Update entity property with annotation value and return set of old/new value
     *
     * @param object $entity
     * @param string $className
     * @param string $propertyName
     * @param \CDSRC\Libraries\Traceable\Annotations\Traceable $annotation
     *
     * @return array
     */
    protected function updateEntityPropertyValue(&$entity, $className, $propertyName, Traceable $annotation)
    {
        $property = new \ReflectionProperty($className, $propertyName);
        $property->setAccessible(true);
        $type = $this->getPropertyType($className, $propertyName, $property);
        $oldValue = $property->getValue($entity);
        $value = $annotation->getValue($type, $entity);
        $property->setValue($entity, $value);

        return array($oldValue, $value);
    }

    /**
     * Get property type
     *
     * @param string $className
     * @param string $propertyName
     * @param \ReflectionProperty $property
     *
     * @return string
     *
     * @throws \CDSRC\Libraries\Traceable\Exceptions\VarAnnotationNotFoundException
     */
    protected function getPropertyType($className, $propertyName, \ReflectionProperty $property = null)
    {
        if (!isset(self::$storedProperties[$className]['types'][$propertyName])) {
            $property = $property === null ? new \ReflectionProperty($className, $propertyName) : $property;
            $matches = array();
            if (!preg_match('/@var(.*)/m', $property->getDocComment(), $matches) || strlen($type = trim($matches[1])) === 0) {
                throw new VarAnnotationNotFoundException('@var annotation not found for "' . $propertyName . '"', 1439252004);
            }
            self::$storedProperties[$className]['types'][$propertyName] = $type;
        }

        return self::$storedProperties[$className]['types'][$propertyName];
    }

    /**
     * Flush for update
     *
     * @param object $entity
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Doctrine\ORM\UnitOfWork $unitOfWork
     */
    protected function onFlushForUpdates(&$entity, EntityManager &$entityManager, UnitOfWork &$unitOfWork)
    {
        $className = get_class($entity);
        $this->initializeAnnotationsForEntity($className);
        if (count(self::$storedProperties[$className]['annotations']) > 0) {
            foreach (self::$storedProperties[$className]['annotations'] as $propertyName => &$annotations) {
                /* @var $annotation Traceable */
                foreach ($annotations as $annotation) {
                    $run = $annotation->on === 'update';
                    if ($annotation->on === 'change') {
                        $changeSet = $unitOfWork->getEntityChangeSet($entity);
                        if (isset($changeSet[$annotation->field])) {
                            $type = $this->getPropertyType($className, $annotation->field);
                            $values = $annotation->getFieldValues($type, $entity);
                            $run = (count($values) === 0 || in_array($changeSet[$annotation->field][1], $values));
                        }
                    }
                    if ($run) {
                        list($oldValue, $value) = $this->updateEntityPropertyValue($entity, $className, $propertyName, $annotation);
                        $entityManager->persist($entity);
                        $unitOfWork->propertyChanged($entity, $propertyName, $oldValue, $value);
                        $unitOfWork->scheduleExtraUpdate($entity, array(
                            $propertyName => array($oldValue, $value)
                        ));
                        break;
                    }
                }
            }
        }
    }
}
