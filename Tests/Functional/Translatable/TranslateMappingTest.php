<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable;

use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Category;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\CategoryRepository;
use Neos\Error\Messages\Result;
use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\I18n\Locale;
use Neos\Flow\Mvc\Controller\Argument;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Property\Exception as FlowPropertyException;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;
use Neos\Flow\Security\Exception as FlowSecurityException;
use Neos\Flow\Tests\FunctionalTestCase;
use Neos\Flow\Validation\Exception\InvalidValidationConfigurationException;
use Neos\Flow\Validation\Exception\InvalidValidationOptionsException;
use Neos\Flow\Validation\Exception\NoSuchValidatorException;
use Neos\Flow\Validation\ValidatorResolver;

/**
 * Test case for translation
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class TranslateMappingTest extends FunctionalTestCase
{

    /**
     * @var boolean
     */
    static protected $testablePersistenceEnabled = true;

    /**
     * @var ValidatorResolver
     */
    protected ValidatorResolver $validatorResolver;

    /**
     *
     * @var PropertyMapper
     */
    protected PropertyMapper $propertyMapper;

    /** @var Locale */
    protected Locale $localeDe;

    /** @var Locale */
    protected Locale $localeFr;

    /** @var Locale */
    protected Locale $localeEn;

    /** @var Locale */
    protected Locale $localeIt;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        if (!$this->persistenceManager instanceof PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }
        $this->propertyMapper = $this->objectManager->get('Neos\Flow\Property\PropertyMapper');
        $this->validatorResolver = $this->objectManager->get('Neos\Flow\Validation\ValidatorResolver');

        $this->localeDe = new Locale('de-CH');
        $this->localeFr = new Locale('fr-CH');
        $this->localeEn = new Locale('en-US');
        $this->localeIt = new Locale('it-IT');
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws FlowPropertyException
     * @throws FlowSecurityException
     */
    public function testMappingMagic()
    {
        $deTitle = 'Title DE';
        $frTitle = 'Title FR';
        $enTitle = 'Title EN';
        $itTitle = 'Title IT';
        $data = array(
            (string)$this->localeDe => array(
                $this->localeDe,
                $deTitle
            ),
            (string)$this->localeFr => array(
                $this->localeFr,
                $frTitle
            ),
            (string)$this->localeEn => array(
                $this->localeEn,
                $enTitle
            ),
            (string)$this->localeIt => array(
                $this->localeIt,
                $itTitle
            ),
        );
        $category = array(
            'icon' => 'test',
            'color' => '#ffffff',
            'title' => array(
                (string)$this->localeDe => $deTitle,
                (string)$this->localeFr => $frTitle,
                (string)$this->localeEn => $enTitle,
                (string)$this->localeIt => $itTitle,
            )
        );
        $argument = new Argument('category', Category::class);
        $argument->setRequired(true);

        $propertyMappingConfiguration = $argument->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowProperties('icon', 'color');
        $propertyMappingConfiguration->setTypeConverterOption('Neos\\Flow\\Property\\TypeConverter\\PersistentObjectConverter', PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);

        $argument->setValue($category);

        /** @var Category $categoryObject */
        $categoryObject = $argument->getValue();

        foreach ($data as $localeAndTitle) {
            $translation = $categoryObject->getTranslationByLocale($localeAndTitle[0], false);
            $this->assertNotNull($translation);
            if ($translation) {
                $this->assertEquals($localeAndTitle[1], $translation->getTitle());
            }
        }
    }

    /**
     * @test
     *
     * @throws FlowPropertyException
     * @throws FlowSecurityException
     * @throws IllegalObjectTypeException
     */
    public function testMappingExistingTranslation()
    {
        $category = new Category();
        $category->setColor('#cccccc');
        $category->setIcon('none');
        $category->setCurrentLocale($this->localeEn, true)->setTitle('Title EN saved');
        $category->setCurrentLocale($this->localeFr, true)->setTitle('Title FR saved');

        $repo = new CategoryRepository();
        $repo->add($category);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();

        $this->assertEquals(2, $category->getTranslations()->count());

        $data = array(
            '__identity' => $this->persistenceManager->getIdentifierByObject($category),
            'icon' => 'new icon',
            'color' => 'black',
            'title' => array(
                (string)$this->localeEn => 'Title EN updated',
                (string)$this->localeDe => 'Title DE new',
            )
        );

        $argument = new Argument('category', Category::class);
        $argument->setRequired(true);

        $propertyMappingConfiguration = $argument->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowProperties('icon', 'color');
        $propertyMappingConfiguration->setTypeConverterOption('Neos\\Flow\\Property\\TypeConverter\\PersistentObjectConverter', PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);

        $argument->setValue($data);

        /** @var Category $updatedCategory */
        $updatedCategory = $argument->getValue();

        $this->assertEquals(2, $updatedCategory->getTranslations()->count());

        $this->assertEquals('Title EN updated', $updatedCategory->setCurrentLocale($this->localeEn)->getTitle());
        $this->assertNull($updatedCategory->setCurrentLocale($this->localeFr)->getCurrentLocale());
        $this->assertEquals('Title DE new', $updatedCategory->setCurrentLocale($this->localeDe)->getTitle());
    }

    /**
     * @test
     *
     * @throws FlowPropertyException
     * @throws FlowSecurityException
     * @throws InvalidValidationConfigurationException
     * @throws InvalidValidationOptionsException
     * @throws NoSuchValidatorException
     */
    public function testValidationResultRewriting()
    {
        $argument = new Argument('category', Category::class);
        $argument->setRequired(true);

        $propertyMappingConfiguration = $argument->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowProperties('icon', 'color');
        $propertyMappingConfiguration->setTypeConverterOption('Neos\\Flow\\Property\\TypeConverter\\PersistentObjectConverter', PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);

        $argument->setValidator($this->validatorResolver->getBaseValidatorConjunction($argument->getDataType()));

        $argument->setValue(array(
            'icon' => 'test',
            'color' => '#ffffff',
            'title' => array(
                (string)$this->localeDe => str_repeat('x', 201),
                (string)$this->localeFr => str_repeat('x', 200),
                (string)$this->localeEn => str_repeat('x', 20),
                (string)$this->localeIt => '',
            )
        ));

        /** @var Result $validationResults */
        $validationResults = $argument->getValidationResults();

        $this->assertTrue($validationResults->hasMessages());

        $this->assertTrue($validationResults->forProperty('title.' . $this->localeDe)->hasErrors());
        $this->assertFalse($validationResults->forProperty('title.' . $this->localeFr)->hasErrors());
        $this->assertFalse($validationResults->forProperty('title.' . $this->localeEn)->hasErrors());
        $this->assertTrue($validationResults->forProperty('title.' . $this->localeIt)->hasErrors());

        $this->assertEquals('This text may not exceed %1$d characters.', $validationResults->forProperty('title.' . $this->localeDe)->getFirstError()->getMessage());
        $this->assertEquals('This property is required.', $validationResults->forProperty('title.' . $this->localeIt)->getFirstError()->getMessage());

        $argument->setValue(array(
            'icon' => '',
            'color' => '',
            'title' => array(
                (string)$this->localeDe => str_repeat('x', 201),
                (string)$this->localeFr => str_repeat('x', 200),
                (string)$this->localeEn => str_repeat('x', 20),
                (string)$this->localeIt => '',
            )
        ));

        /** @var Result $validationResults */
        $validationResults = $argument->getValidationResults();

        $this->assertTrue($validationResults->forProperty('title.' . $this->localeDe)->hasErrors());
        $this->assertFalse($validationResults->forProperty('title.' . $this->localeFr)->hasErrors());
        $this->assertFalse($validationResults->forProperty('title.' . $this->localeEn)->hasErrors());
        $this->assertTrue($validationResults->forProperty('title.' . $this->localeIt)->hasErrors());
        $this->assertTrue($validationResults->forProperty('color')->hasErrors());

        $this->assertEquals('This text may not exceed %1$d characters.', $validationResults->forProperty('title.' . $this->localeDe)->getFirstError()->getMessage());
        $this->assertEquals('This property is required.', $validationResults->forProperty('title.' . $this->localeIt)->getFirstError()->getMessage());
        $this->assertEquals('This property is required.', $validationResults->forProperty('color')->getFirstError()->getMessage());

    }
}
