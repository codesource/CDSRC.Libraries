<?php

namespace CDSRC\Libraries\Tests\Functional\Translatable;

/* *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Entity;

/**
 * Testcase for persistence
 *
 */
class PersistenceTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

    /**
     * @var boolean
     */
    static protected $testablePersistenceEnabled = TRUE;

    /**
     * @var \CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\GenericRepository
     */
    protected $genericRepository;

    /**
     * @var \CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\SpecificRepository
     */
    protected $specificRepository;

    /**
     * @var \CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\EntityRepository
     */
    protected $entityRepository;

    /**
     * Generated datas for testing
     * @var array
     */
    protected $datas;

    /**
     * @return void
     */
    public function setUp() {
        parent::setUp();
        if (!$this->persistenceManager instanceof \TYPO3\Flow\Persistence\Doctrine\PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }
        $this->entityRepository = $this->objectManager->get('CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\EntityRepository');
        $this->genericRepository = $this->objectManager->get('CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\GenericRepository');
        $this->specificRepository = $this->objectManager->get('CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\SpecificRepository');

        $this->generateDatas();
    }

    /**
     * @test
     */
    public function genericEntitiesArePersistedAndReconstituted() {
//        $this->removeExampleEntities();
        $this->insertExampleEntity('Generic');

        $entity = $this->genericRepository->findAll()->getFirst();
        $entity->setFallbackOnTranslation(FALSE);
        foreach ($this->datas as $type => $values) {
            foreach ($values as $locale => $value) {
                if ($locale === 'default') {
                    $entity->setDefaultLocaleForTranslation();
                } else {
                    $entity->setLocaleForTranslation($locale);
                }
                $this->assertEquals($value, $entity->$type);
            }
        }
    }

    /**
     * @test
     */
    public function specificEntitiesArePersistedAndReconstituted() {
//        $this->removeExampleEntities();
        $this->insertExampleEntity('Specific');

        $entity = $this->specificRepository->findAll()->getFirst();
        $entity->setFallbackOnTranslation(FALSE);
        foreach ($this->datas as $type => $values) {
            foreach ($values as $locale => $value) {
                if ($locale === 'default') {
                    $entity->setDefaultLocaleForTranslation();
                } else {
                    $entity->setLocaleForTranslation($locale);
                }
                $this->assertEquals($value, $entity->$type);
            }
        }
    }

    /**
     * @test
     */
    public function genericAndSpecificAreSame() {
        $this->insertExampleEntity('Generic');
        $this->insertExampleEntity('Specific');

        $genericEntity = $this->specificRepository->findAll()->getFirst();
        $specificEntity = $this->specificRepository->findAll()->getFirst();
        $genericEntity->setFallbackOnTranslation(FALSE);
        $specificEntity->setFallbackOnTranslation(FALSE);
        $fields = array('string', 'boolean', 'integer', 'float', 'date', 'array', 'object');
        $locales = array('default', 'en-US', 'fr-FR', 'unknown', 'fallback');
        foreach ($locales as $locale) {
            if ($locale === 'default') {
                $genericEntity->setDefaultLocaleForTranslation();
                $specificEntity->setDefaultLocaleForTranslation();
            } else {
                if ($locale === 'fallback') {
                    $genericEntity->setFallbackOnTranslation(FALSE);
                    $specificEntity->setFallbackOnTranslation(FALSE);
                }
                $genericEntity->setLocaleForTranslation($locale);
                $specificEntity->setLocaleForTranslation($locale);
            }
            foreach ($fields as $field) {
                $this->assertEquals($genericEntity->$field, $specificEntity->$field);
            }
        }
    }

    /**
     * dataProvider for translation testing
     * 
     */
    public function generateDatas() {
        $entityDefault = new Entity('default');
        $entityEnUs = new Entity('en-US');
        $entityFrFr = new Entity('fr-FR');
        $this->entityRepository->add($entityDefault);
        $this->entityRepository->add($entityEnUs);
        $this->entityRepository->add($entityFrFr);
        $now = time();
        $this->datas = array(
            'string' => array(
                'default' => 'default value',
                'en-US' => 'value for en-US',
                'fr-FR' => 'value for fr-FR'
            ),
            'boolean' => array(
                'default' => rand(0, 1) ? TRUE : FALSE,
                'en-US' => rand(0, 1) ? TRUE : FALSE,
                'fr-FR' => rand(0, 1) ? TRUE : FALSE
            ),
            'integer' => array(
                'default' => rand(1000, 10000),
                'en-US' => rand(1000, 10000),
                'fr-FR' => rand(1000, 10000)
            ),
            'float' => array(
                'default' => rand(1000, 10000) / 100,
                'en-US' => rand(1000, 10000) / 100,
                'fr-FR' => rand(1000, 10000) / 100
            ),
            'date' => array(
                'default' => (new \DateTime())->setTimestamp($now + rand(1000, 10000)),
                'en-US' => (new \DateTime())->setTimestamp($now + rand(1000, 10000)),
                'fr-FR' => (new \DateTime())->setTimestamp($now + rand(1000, 10000))
            ),
            'object' => array(
                'default' => $entityDefault,
                'en-US' => $entityEnUs,
                'fr-FR' => $entityFrFr
            )
        );
        $dataArray = array();
        foreach ($this->datas as $type => $values) {
            foreach ($values as $locale => $value) {
                $dataArray[$locale][$type] = $value;
            }
        }
        $this->datas['array'] = $dataArray;
        $this->datas['array']['default']['array'] = $dataArray['default'];
        $this->datas['array']['en-US']['array'] = $dataArray['en-US'];
        $this->datas['array']['fr-FR']['array'] = $dataArray['fr-FR'];
    }

    /**
     * Helper which inserts example data into the database.
     *
     * @param string $type
     * 
     */
    protected function insertExampleEntity($type = 'Generic') {
        switch ($type) {
            case 'Specific':
                $entity = new Fixture\Model\Specific();
                $repository = & $this->specificRepository;
                break;
            default:
                $entity = new Fixture\Model\Generic();
                $repository = & $this->genericRepository;
                break;
        }
        foreach ($this->datas as $type => $values) {
            foreach ($values as $locale => $value) {
                if ($locale === 'default') {
                    $entity->setDefaultLocaleForTranslation();
                } else {
                    $entity->setLocaleForTranslation($locale);
                }
                $entity->$type = $value;
            }
        }
        $repository->add($entity);

        $this->persistenceManager->persistAll();
    }

    /**
     * Remove all example entities to enforce a clean state
     */
    protected function removeExampleEntities() {
        $this->genericRepository->removeAll();
        $this->specificRepository->removeAll();
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();
    }

}
