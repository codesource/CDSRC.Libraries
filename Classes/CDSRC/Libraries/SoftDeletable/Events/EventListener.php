<?php

namespace CDSRC\Libraries\SoftDeletable\Events;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable;
use CDSRC\Libraries\SoftDeletable\Exceptions\PropertyNotFoundException;
use Doctrine\ORM\Event\OnFlushEventArgs;
use TYPO3\Flow\Annotations as Flow;

/**
 * 
 */
class EventListener {
    
    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    public function onFlush(OnFlushEventArgs $eventArgs) {

        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $properties = array();

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            $className = get_class($entity);
            $annotation = $this->reflectionService->getClassAnnotation($className, SoftDeletable::class);
            if ($annotation !== NULL) {
                if(!isset($properties[$className])){
                    $existingProperties = $this->reflectionService->getClassPropertyNames($className);
                    if(!in_array($annotation->deleteProperty, $existingProperties)){
                        throw new PropertyNotFoundException("Property '" . $annotation->deleteProperty . "' not found for '" . $className . "'", 1439207432);
                    }
                    $reflectionClass = new \ReflectionClass($className);
                    $properties[$className] = array();
                    if(strlen($annotation->hardDeleteProperty) > 0){
                        if(!in_array($annotation->hardDeleteProperty, $existingProperties)){
                            throw new PropertyNotFoundException("Property '" . $annotation->hardDeleteProperty . "' not found for '" . $className . "'", 1439207431);
                        }
                        $properties[$className]['h'] = $reflectionClass->getProperty($annotation->hardDeleteProperty);
                        $properties[$className]['h']->setAccessible(TRUE);
                    }
                    $properties[$className]['p'] = $reflectionClass->getProperty($annotation->deleteProperty);
                    $properties[$className]['p']->setAccessible(TRUE);
                }
                if(isset($properties[$className]['h']) && $properties[$className]['h']->getValue($entity)){
                    continue;
                }
                
                $oldValue = $properties[$className]['p']->getValue($entity);
                
                $date = new \DateTime();
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
