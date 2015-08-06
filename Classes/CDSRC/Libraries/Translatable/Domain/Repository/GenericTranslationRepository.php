<?php

namespace CDSRC\Libraries\Translatable\Domain\Repository;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 * 
 */

use TYPO3\Flow\Annotations as Flow;

/**
 * Global translation repository
 *
 * @Flow\Scope("singleton")
 */
class GenericTranslationRepository extends \TYPO3\Flow\Persistence\Repository {

    /**
     * Find by object and locale
     *
     * @param string $method Name of the method
     * @param array $arguments The arguments
     * @return mixed The result of the repository method
     * @api
     */
    public function findByObjectAndLocale($object, $locale, $cacheQuery = TRUE) {
        $query = $this->createQuery();
        $reference = $this->persistenceManager->getIdentifierByObject($object);
        if ($reference !== NULL) {
            return $query
                            ->matching($query->equals('referenceToObject', $reference, TRUE))
                            ->matching($query->equals('classnameOfObject', get_class($object), TRUE))
                            ->matching($query->equals('currentLocale', (string) $locale, TRUE))
                            ->execute($cacheQuery)->getFirst();
        }
        return NULL;
    }

}
