<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable;

use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Entity;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\EntityRepository;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\GenericRepository;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\SpecificRepository;
use DateTime;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Test case for persistence
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * @method assertEquals($value, $expected)
 * @method markTestSkipped($message)
 * @method markTestIncomplete($message)
 */
class PersistenceTest extends FunctionalTestCase
{

    /**
     * @var boolean
     */
    static protected $testablePersistenceEnabled = TRUE;

    /**
     * @var GenericRepository
     */
    protected GenericRepository $genericRepository;

    /**
     * @var SpecificRepository
     */
    protected SpecificRepository $specificRepository;

    /**
     * @var EntityRepository
     */
    protected EntityRepository $entityRepository;

    /**
     * Generated data for testing
     * @var array
     */
    protected array $data;

    /**
     * @return void
     *
     * @throws IllegalObjectTypeException
     */
    public function setUp(): void
    {
        parent::setUp();
        if (!$this->persistenceManager instanceof PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }
        $this->entityRepository = $this->objectManager->get('CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\EntityRepository');
        $this->genericRepository = $this->objectManager->get('CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\GenericRepository');
        $this->specificRepository = $this->objectManager->get('CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\SpecificRepository');

        $this->generateData();
    }

    /**
     * @test
     *
     * @throws IllegalObjectTypeException
     */
    public function genericEntitiesArePersistedAndReconstituted()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet with new feature.'
        );
//        $this->removeExampleEntities();
        $this->insertExampleEntity('Generic');

        $entity = $this->genericRepository->findAll()->getFirst();
        $entity->setFallbackOnTranslation(FALSE);
        foreach ($this->data as $type => $values) {
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
     *
     * @throws IllegalObjectTypeException
     */
    public function specificEntitiesArePersistedAndReconstituted()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet with new feature.'
        );
//        $this->removeExampleEntities();
        $this->insertExampleEntity('Specific');

        $entity = $this->specificRepository->findAll()->getFirst();
        $entity->setFallbackOnTranslation(FALSE);
        foreach ($this->data as $type => $values) {
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
     *
     * @throws IllegalObjectTypeException
     */
    public function genericAndSpecificAreSame()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet with new feature.'
        );
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
     * @throws IllegalObjectTypeException
     */
    public function generateData()
    {
        $entityDefault = new Entity('default');
        $entityEnUs = new Entity('en-US');
        $entityFrFr = new Entity('fr-FR');
        $this->entityRepository->add($entityDefault);
        $this->entityRepository->add($entityEnUs);
        $this->entityRepository->add($entityFrFr);
        $now = time();
        $this->data = array(
            'string' => array(
                'default' => 'default value',
                'en-US' => 'value for en-US',
                'fr-FR' => 'value for fr-FR'
            ),
            'boolean' => array(
                'default' => (bool)rand(0, 1),
                'en-US' => (bool)rand(0, 1),
                'fr-FR' => (bool)rand(0, 1)
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
                'default' => (new DateTime())->setTimestamp($now + rand(1000, 10000)),
                'en-US' => (new DateTime())->setTimestamp($now + rand(1000, 10000)),
                'fr-FR' => (new DateTime())->setTimestamp($now + rand(1000, 10000))
            ),
            'object' => array(
                'default' => $entityDefault,
                'en-US' => $entityEnUs,
                'fr-FR' => $entityFrFr
            )
        );
        $dataArray = array();
        foreach ($this->data as $type => $values) {
            foreach ($values as $locale => $value) {
                $dataArray[$locale][$type] = $value;
            }
        }
        $this->data['array'] = $dataArray;
        $this->data['array']['default']['array'] = $dataArray['default'];
        $this->data['array']['en-US']['array'] = $dataArray['en-US'];
        $this->data['array']['fr-FR']['array'] = $dataArray['fr-FR'];
    }

    /**
     * Helper which inserts example data into the database.
     *
     * @param string $type
     *
     * @throws IllegalObjectTypeException
     * @noinspection PhpExpressionResultUnusedInspection
     */
    protected function insertExampleEntity(string $type = 'Generic'): void
    {
        switch ($type) {
            case 'Specific':
                $entity = new Fixture\Model\Specific();
                $repository = &$this->specificRepository;
                break;
            default:
                $entity = new Fixture\Model\Generic();
                $repository = &$this->genericRepository;
                break;
        }
        foreach ($this->data as $type => $values) {
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
    protected function removeExampleEntities()
    {
        $this->genericRepository->removeAll();
        $this->specificRepository->removeAll();
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();
    }

}
