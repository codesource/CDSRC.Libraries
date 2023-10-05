<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Model;

use Doctrine\Common\Collections\Collection;
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
    public function addTranslation(TranslationInterface $translation): static;

    /**
     * Remove a translation
     *
     * @param TranslationInterface $translation
     *
     * @return TranslatableInterface
     */
    public function removeTranslation(TranslationInterface $translation): static;

    /**
     * Remove a translation by locale
     *
     * @param Locale $locale
     *
     * @return TranslatableInterface
     */
    public function removeTranslationByLocale(Locale $locale): static;

    /**
     * Remove all translations
     *
     * @return TranslatableInterface
     */
    public function removeAllTranslations(): static;

    /**
     * Replace all translations by the given collection
     *
     * @param Collection<TranslationInterface> $translations
     *
     * @return TranslatableInterface
     */
    public function setTranslations(Collection $translations): static;

    /**
     * Get all translations
     *
     * @return Collection<TranslationInterface>
     */
    public function getTranslations(): Collection;

    /**
     * Get translation class name
     *
     * @return string
     */
    public function getTranslationClassName(): string;

    /**
     * Get fallback on translation status
     *
     * @return bool
     */
    public function getFallbackOnTranslation(): bool;

    /**
     * Check if object has a translation for a specific locale
     *
     * @param Locale $locale
     *
     * @return bool
     */
    public function hasTranslationForLocale(Locale $locale): bool;

    /**
     * Set fallback on translation status
     *
     * @param bool $fallback
     *
     * @return TranslatableInterface
     */
    public function setFallbackOnTranslation(bool $fallback): static;

    /**
     * Return unannotated translatable fields
     * NOTICE: This function should be override in sub classes if needed.
     *
     * @return array
     */
    public static function getTranslatableFields(): array;

    /**
     * Return the list of all locales that have a translation
     *
     * @return array
     */
    public function getAvailableLocales(): array;
}
