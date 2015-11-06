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
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\I18n\Locale;

/**
 * Abstract class for translatable entities
 *
 * @Flow\Entity
 * @ORM\InheritanceType("JOINED")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractTranslatable implements TranslatableInterface
{

    /**
     *
     * @var string
     * @Flow\Transient
     */
    protected $translationClassName = null;

    /**
     * Will fallback to default language if no translation found
     *
     * @var boolean
     * @Flow\Transient
     */
    protected $fallbackOnTranslation = true;

    /**
     * List of translations
     *
     * @var \Doctrine\Common\Collections\Collection<\CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation>
     * @ORM\OneToMany(mappedBy="i18nParent", cascade={"all"}, orphanRemoval=true)
     */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get translation class name
     *
     * @return string
     */
    public function getTranslationClassName()
    {
        if ($this->translationClassName === NULL){
            $specificTranslationClassName = get_called_class().'Translation';
            if(class_exists($specificTranslationClassName)){
                $this->translationClassName = $specificTranslationClassName;
            }else{
                $this->translationClassName = 'CDSRC\Libraries\Translatable\Domain\Model\GenericTranslation';
            }
        }
        return $this->translationClassName;
    }

    /**
     * Get fallback on translation status
     */
    public function getFallbackOnTranslation()
    {
        $this->fallbackOnTranslation;
    }

    /**
     * Set fallback on translation status
     *
     * @param boolean $fallback
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function setFallbackOnTranslation($fallback)
    {
        $this->fallbackOnTranslation = $fallback;

        return $this;
    }

    /**
     * Check if object has a translation for a specific locale
     *
     * @param \TYPO3\Flow\I18n\Locale $locale
     *
     * @return boolean
     */
    public function hasTranslationForLocale(Locale $locale = null)
    {
        foreach ($this->translations as $translation) {
            if ($translation->getI18nLocale() === $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a new translation to object
     * Notice: Only one translation by locale and object should exists
     *
     * @param \CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface $translation
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function addTranslation(TranslationInterface $translation)
    {
        if (!($this->translations->contains($translation) || $this->hasTranslationForLocale($translation->getI18nLocale()))) {
            $this->translations->add($translation);
            $translation->setI18nParent($this);
        }

        return $this;
    }

    /**
     * Remove a translation by object
     *
     * @param \CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface $translation
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function removeTranslation(TranslationInterface $translation)
    {
        if ($this->translations->contains($translation)) {
            $this->translations->removeElement($translation);
        }

        return $this;
    }

    /**
     * Remove a translation by locale
     *
     * @param \TYPO3\Flow\I18n\Locale $locale
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function removeTranslationByLocale(Locale $locale)
    {
        foreach ($this->translations as $translation) {
            if ($translation->getI18nLocale() === $locale) {
                $this->translations->removeElement($translation);
            }
        }

        return $this;
    }

    /**
     * Remove all translations
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function removeAllTranslations()
    {
        foreach ($this->translations as $translation) {
            $this->removeTranslation($translation);
        }

        return $this;
    }

    /**
     * Replace all translations by the given collection
     *
     * @param ArrayCollection<\CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface> $translations
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function setTranslations(ArrayCollection $translations)
    {
        foreach ($this->translations as $translation) {
            if (!$translations->contains($translation)) {
                $this->removeTranslation($translation);
            }
        }
        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }

        return $this;
    }

    /**
     * Get all translations
     *
     * @return \Doctrine\Common\Collections\ArrayCollection<\CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface>
     */
    public function getTranslations()
    {
        return $this->translations;
    }

}
