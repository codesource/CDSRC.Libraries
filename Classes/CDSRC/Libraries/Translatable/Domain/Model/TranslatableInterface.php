<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Neos\Flow\I18n\Locale;

/**
 * Make an object translatable
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
interface TranslatableInterface
{

    /**
     * Add a new translation to object
     * Notice: Only one translation by locale and object should exists
     *
     * @param TranslationInterface $translation
     *
     * @return TranslatableInterface
     */
    public function addTranslation(TranslationInterface $translation);

    /**
     * Remove a translation
     *
     * @param TranslationInterface $translation
     *
     * @return TranslatableInterface
     */
    public function removeTranslation(TranslationInterface $translation);

    /**
     * Remove a translation by locale
     *
     * @param Locale $locale
     *
     * @return TranslatableInterface
     */
    public function removeTranslationByLocale(Locale $locale);

    /**
     * Remove all translations
     *
     * @return TranslatableInterface
     */
    public function removeAllTranslations();

    /**
     * Replace all translations by the given collection
     *
     * @param \Doctrine\Common\Collections\ArrayCollection<\CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface> $translations
     *
     * @return TranslatableInterface
     */
    public function setTranslations(ArrayCollection $translations);

    /**
     * Get all translations
     *
     * @return \Doctrine\Common\Collections\ArrayCollection<\CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface>
     */
    public function getTranslations();

    /**
     * Get translation class name
     *
     * @return string
     */
    public function getTranslationClassName();

    /**
     * Get fallback on translation status
     *
     * @return boolean
     */
    public function getFallbackOnTranslation();

    /**
     * Check if object has a translation for a specific locale
     *
     * @param Locale $locale
     *
     * @return boolean
     */
    public function hasTranslationForLocale(Locale $locale);

    /**
     * Set fallback on translation status
     *
     * @param boolean $fallback
     *
     * @return TranslatableInterface
     */
    public function setFallbackOnTranslation($fallback);

    /**
     * Return unannotated translatable fields
     * NOTICE: This function should be override in sub classes if needed.
     *
     * @return array
     */
    public static function getTranslatableFields();

    /**
     * Return the list of all locales that have a translation
     *
     * @return array
     */
    public function getAvailableLocales();
}
