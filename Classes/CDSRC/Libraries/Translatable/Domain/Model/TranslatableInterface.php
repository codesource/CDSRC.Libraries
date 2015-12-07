<?php

namespace CDSRC\Libraries\Translatable\Domain\Model;

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

use Doctrine\Common\Collections\ArrayCollection;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\I18n\Locale;

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
     * @param \CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface $translation
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function addTranslation(TranslationInterface $translation);

    /**
     * Remove a translation
     *
     * @param \CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface $translation
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function removeTranslation(TranslationInterface $translation);

    /**
     * Remove a translation by locale
     *
     * @param \TYPO3\Flow\I18n\Locale $locale
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function removeTranslationByLocale(Locale $locale);

    /**
     * Remove all translations
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function removeAllTranslations();

    /**
     * Replace all translations by the given collection
     *
     * @param \Doctrine\Common\Collections\ArrayCollection<\CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface> $translations
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function setTranslations(ArrayCollection $translations);

    /**
     * Get all translations
     *
     * @return \Doctrine\Common\Collections\ArrayCollection<\CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface>
     */
    public function getTranslations();

    /**
     * Get fallback on translation status
     *
     * @return boolean
     */
    public function getFallbackOnTranslation();

    /**
     * Check if object has a translation for a specific locale
     *
     * @param \TYPO3\Flow\I18n\Locale $locale
     *
     * @return boolean
     */
    public function hasTranslationForLocale(Locale $locale);

    /**
     * Set fallback on translation status
     *
     * @param boolean $fallback
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function setFallbackOnTranslation($fallback);


    /**
     * Return unannotated translatable fields
     *
     * @return array
     */
    public static function getTranslatableFields();
}
