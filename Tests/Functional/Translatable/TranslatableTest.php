<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable;

use Neos\Flow\I18n\Locale;
use Neos\Flow\Tests\FunctionalTestCase;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Category;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\CategoryTranslation;
use CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository\CategoryRepository;

/**
 * Test case for translatable entities.
 */
class TranslatableTest extends FunctionalTestCase
{
    static protected $testablePersistenceEnabled = true;

    /** @var Locale */
    protected $localeDe;

    /** @var Locale */
    protected $localeFr;

    /** @var Locale */
    protected $localeEn;

    /** @var Locale */
    protected $localeIt;

    public function setUp()
    {
        parent::setUp();

        $this->localeDe = new Locale('de-CH');
        $this->localeFr = new Locale('fr-CH');
        $this->localeEn = new Locale('en-US');
        $this->localeIt = new Locale('it-IT');
    }

    public function testTranslatable()
    {
        // Create the main entity
        $category = new Category();
        $category->setColor('foobar-color');
        $category->setIcon('foobar-icon');

        // Setup translations
        $trxFr = new CategoryTranslation($this->localeFr);
        $trxEn = new CategoryTranslation($this->localeEn);
        $category->addTranslation($trxFr);
        $category->addTranslation($trxEn);

        // Try to get an non-existing translation object (without forcing the creation)
        $trxIt = $category->getTranslationByLocale($this->localeIt);
        $this->assertNull($trxIt);

        // Translate the data (and create a new translation object for it-IT)
        $trxIt = $category->getTranslationByLocale($this->localeIt, true);
        $trxIt->setTitle('test IT');
        $trxFr->setTitle('test FR');
        $trxEn->setTitle('test EN');

        // Persist the entity
        $repo = new CategoryRepository();
        $repo->add($category);
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();

        // Read the entity from the DB and assert it's the same as the one we persisted above
        /** @var Category $res */
        $res = $repo->findOneByColor('foobar-color');

        $this->assertNotNull($res, 'The translatable entity was not persisted properly');
        $this->assertInstanceOf('CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Category', $res);
        $this->assertEquals('foobar-icon', $res->getIcon());

        $trx = $res->getTranslationByLocale($this->localeIt);
        $this->assertTrue((bool)$trx);
        $this->assertInstanceOf('CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\CategoryTranslation', $trx);
        $this->assertEquals('test IT', $trx->getTitle());

        // For the other languages we know they exist, quickly test the translated value
        $this->assertEquals('test FR', $res->getTranslationByLocale($this->localeFr)->getTitle());
        $this->assertEquals('test EN', $res->getTranslationByLocale($this->localeEn)->getTitle());

        // Try to get an non-existing translation object
        $this->assertNull($res->getTranslationByLocale(new Locale('en-GB')));
    }

    /**
     * @covers Category::setCurrentLocale
     * @covers Category::getCurrentLocale
     */
    public function testSetCurrentLocale()
    {
        $foobarTextEn = 'foobar in english';

        $category = new Category();
        $category->addTranslation(new CategoryTranslation($this->localeEn));
        $category->addTranslation(new CategoryTranslation($this->localeFr));

        // No current locale has been set
        $this->assertNull($category->getCurrentLocale());
        $this->assertNull($category->getTitle());

        // Set a current locale and check everything works as expected
        $category->setCurrentLocale($this->localeEn);
        $category->setTitle($foobarTextEn);
        $this->assertEquals($foobarTextEn, $category->getTitle());

        $trx = $category->getTranslationByLocale($this->localeEn);
        $this->assertEquals($foobarTextEn, $trx->getTitle());
        $this->assertEquals($this->localeEn, $category->getCurrentLocale());

        // Set another locale and check the results are different
        $category->setCurrentLocale($this->localeFr);
        $this->assertNotEquals($foobarTextEn, $category->getTitle());

        // Use a non-existing locale without forcing the creation and check the title cannot be set
        $category->setCurrentLocale($this->localeDe);
        $category->setTitle('foobar');
        $this->assertNull($category->getTitle());

        // Now force the creation of the locale and try again
        $category->setCurrentLocale($this->localeDe, true);
        $category->setTitle('foobar');
        $this->assertEquals('foobar', $category->getTitle());
    }

    /**
     * @covers Category::__call
     */
    public function testGetSetWithLocale()
    {
        $myEnText = 'title in english';
        $myFrText = 'titre en français';

        $category = new Category();
        $category->addTranslation(new CategoryTranslation($this->localeEn));

        // Set the translation specifying the locale
        $category->setTitle($myEnText, $this->localeEn);
        $this->assertEquals($myEnText, $category->getTitle($this->localeEn));

        // No locale param given. As we didn't set a default locale, this should be null
        $this->assertNull($category->getTitle());

        // The french translations have not been created so we cannot set it
        $category->setTitle($myFrText, $this->localeFr);
        // TODO: we should have an exception instead of doing nothing
        $this->assertNull($category->getTitle($this->localeFr));

        // Same as above but this time we force the translation
        $category->setTitle($myFrText, $this->localeFr, true);
        $this->assertEquals($myFrText, $category->getTitle($this->localeFr));

        // Now we should be able to create french translations without forcing anything
        $category->setTitle('foobar', $this->localeFr);
        $this->assertEquals('foobar', $category->getTitle($this->localeFr));

        // Check that this integrates with the setCurrentLocale method
        $category->setCurrentLocale($this->localeEn);
        $this->assertEquals($myEnText, $category->getTitle());
    }
}
