<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Traceable\Events;

use CDSRC\Libraries\Exceptions\InvalidValueException;
use CDSRC\Libraries\Traceable\Annotations\Traceable;
use CDSRC\Libraries\Traceable\Exceptions\VarAnnotationNotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\UnitOfWork;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;
use ReflectionException;
use ReflectionProperty;


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
    protected static array $storedProperties = array();

    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected ReflectionService $reflectionService;

    /**
     * Intercept flush event
     *
     * @param OnFlushEventArgs $eventArgs
     *
     * @throws InvalidValueException
     * @throws ORMException
     * @throws ReflectionException
     * @throws VarAnnotationNotFoundException
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getObjectManager();
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
     * @param EntityManager $entityManager
     * @param UnitOfWork $unitOfWork
     *
     * @throws InvalidValueException
     * @throws ReflectionException
     * @throws VarAnnotationNotFoundException
     * @throws ORMException
     */
    protected function onFlushForInsertions(object $entity, EntityManager $entityManager, UnitOfWork $unitOfWork): void
    {
        $className = get_class($entity);
        $this->initializeAnnotationsForEntity($className);
        if (count(self::$storedProperties[$className]['annotations']) > 0) {
            foreach (self::$storedProperties[$className]['annotations'] as $propertyName => $annotations) {
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
    protected function initializeAnnotationsForEntity(string $className): void
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
     * @param Traceable $annotation
     *
     * @return array
     *
     * @throws VarAnnotationNotFoundException
     * @throws ReflectionException
     * @throws InvalidValueException
     */
    protected function updateEntityPropertyValue(object $entity, string $className, string $propertyName, Traceable $annotation): array
    {
        $property = new ReflectionProperty($className, $propertyName);
        $type = $this->getPropertyType($className, $propertyName, $property);
        $oldValue = $property->getValue($entity);
        $types = explode('|', $type);
        $value = null;
        foreach ($types as $subtype) {
            $value = $annotation->getValue($subtype, $entity);
            $valueType = strtolower(gettype($value));
            if (($valueType === 'object' && $value instanceof $subtype) || ($valueType === 'double' && $subtype === 'float') || $valueType === $subtype) {
                break;
            } else {
                $value = null;
            }
        }
        $property->setValue($entity, $value);

        return array($oldValue, $value);
    }

    /**
     * Get property type
     *
     * @param string $className
     * @param string $propertyName
     * @param ReflectionProperty|null $property
     *
     * @return string
     *
     * @throws ReflectionException
     * @throws VarAnnotationNotFoundException
     */
    protected function getPropertyType(string $className, string $propertyName, ?ReflectionProperty $property = null): string
    {
        if (!isset(self::$storedProperties[$className]['types'][$propertyName])) {
            $property = $property === null ? new ReflectionProperty($className, $propertyName) : $property;
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
     * @param EntityManager $entityManager
     * @param UnitOfWork $unitOfWork
     *
     * @throws VarAnnotationNotFoundException
     * @throws InvalidValueException
     * @throws ReflectionException
     * @throws ORMException
     */
    protected function onFlushForUpdates(object $entity, EntityManager $entityManager, UnitOfWork $unitOfWork): void
    {
        $className = get_class($entity);
        $this->initializeAnnotationsForEntity($className);
        if (count(self::$storedProperties[$className]['annotations']) > 0) {
            foreach (self::$storedProperties[$className]['annotations'] as $propertyName => $annotations) {
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
