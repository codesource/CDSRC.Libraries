<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\SoftDeletable\Events;

use CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable;
use CDSRC\Libraries\SoftDeletable\Exceptions\PropertyNotFoundException;
use DateTime;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;
use ReflectionClass;
use ReflectionException;

/**
 * Database event listener
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class EventListener
{

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
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $properties = array();

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            $className = get_class($entity);
            $annotation = $this->reflectionService->getClassAnnotation($className, SoftDeletable::class);
            if ($annotation !== null) {
                if (!isset($properties[$className])) {
                    $existingProperties = $this->reflectionService->getClassPropertyNames($className);
                    if (!in_array($annotation->deleteProperty, $existingProperties)) {
                        throw new PropertyNotFoundException("Property '" . $annotation->deleteProperty . "' not found for '" . $className . "'", 1439207432);
                    }
                    $reflectionClass = new ReflectionClass($className);
                    $properties[$className] = array();
                    if (strlen($annotation->hardDeleteProperty) > 0) {
                        if (!in_array($annotation->hardDeleteProperty, $existingProperties)) {
                            throw new PropertyNotFoundException("Property '" . $annotation->hardDeleteProperty . "' not found for '" . $className . "'", 1439207431);
                        }
                        $properties[$className]['h'] = $reflectionClass->getProperty($annotation->hardDeleteProperty);
                        $properties[$className]['h']->setAccessible(true);
                    }
                    $properties[$className]['p'] = $reflectionClass->getProperty($annotation->deleteProperty);
                    $properties[$className]['p']->setAccessible(true);
                }
                if (isset($properties[$className]['h']) && $properties[$className]['h']->getValue($entity)) {
                    continue;
                }

                $oldValue = $properties[$className]['p']->getValue($entity);

                $date = new DateTime();
                $properties[$className]['p']->setValue($entity, $date);
                $entityManager->persist($entity);
                $unitOfWork->propertyChanged($entity, $annotation->deleteProperty, $oldValue, $date);

                $unitOfWork->scheduleExtraUpdate($entity, array(
                    $annotation->deleteProperty => array($oldValue, $date)
                ));
            }
        }
    }
}
