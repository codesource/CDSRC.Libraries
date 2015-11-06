<?php

namespace CDSRC\Libraries\SoftDeletable\Events;

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
use Doctrine\ORM\Event\OnFlushEventArgs;
use TYPO3\Flow\Annotations as Flow;

/**
 * Database event listener
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class EventListener
{

    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * Intercept flush event
     *
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
     *
     * @throws \CDSRC\Libraries\SoftDeletable\Exceptions\PropertyNotFoundException
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
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
                    $reflectionClass = new \ReflectionClass($className);
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
