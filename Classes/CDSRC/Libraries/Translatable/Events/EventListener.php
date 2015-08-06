<?php

namespace CDSRC\Libraries\Translatable\Events;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use CDSRC\Libraries\Utility\GeneralUtility;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * 
 */
class EventListener {

    public function onFlush(OnFlushEventArgs $eventArgs) {

        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if (GeneralUtility::useTrait($entity, 'CDSRC\\Libraries\\Translatable\\Domain\\Model\\TraitTranslatable')) {
                $translations = $entity->getTranslations(TRUE);
                if(count($translations) > 0){
                    $classMetadata = $entityManager->getClassMetadata(get_class(reset($translations)));
                    foreach($translations as $translation){
                        $entityManager->persist($translation);
                        $unitOfWork->computeChangeSet($classMetadata, $translation);
                    }
                    $unitOfWork->computeChangeSets();
                }
            }
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if (GeneralUtility::useTrait($entity, 'CDSRC\\Libraries\\Translatable\\Domain\\Model\\TraitTranslatable')) {
                $translations = $entity->getTranslations(TRUE);
                if(count($translations) > 0){
                    $classMetadata = $entityManager->getClassMetadata(get_class(reset($translations)));
                    foreach($translations as $translation){
                        $entityManager->persist($translation);
                        $unitOfWork->computeChangeSet($classMetadata, $translation);
                    }
                    $unitOfWork->computeChangeSets();
                }
            }
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            if (GeneralUtility::useTrait($entity, 'CDSRC\\Libraries\\Translatable\\Domain\\Model\\TraitTranslatable')) {
                $translations = $entity->getTranslations(TRUE);
                if(count($translations) > 0){
                    $classMetadata = $entityManager->getClassMetadata(get_class(reset($translations)));
                    foreach($translations as $translation){
                        $entityManager->remove($translation);
                    }
                    $unitOfWork->computeChangeSets();
                }
            }
        }

        foreach ($unitOfWork->getScheduledCollectionDeletions() as $col) {
        }

        foreach ($unitOfWork->getScheduledCollectionUpdates() as $col) {
        }
    }

}
