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
     * Store unannotated translatable fields
     * Notice: if you override this field in child classes, this property has to be transient (otherwise an unneeded
     * field will be created in the DB).
     *
     * @Flow\Transient
     * @var array
     */
    protected $translatableFields = array();

    /**
     * List of translations
     *
     * @var \Doctrine\Common\Collections\Collection<\CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation>
     * @ORM\OneToMany(mappedBy="i18nParent", cascade={"all"}, orphanRemoval=true)
     */
    protected $translations;

    /**
     * The current translation object to use (set it using setCurrentLocale)
     * @Flow\Transient
     * @var TranslationInterface
     */
    protected $curTranslation;


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
    public function hasTranslationForLocale(Locale $locale)
    {
        return $this->getTranslationObjectForLocale($locale) !== false;
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
     * Get the translation object associated with the given locale. If it does not exist and $forceCreation is set, then
     * the translation object is created.
     *
     * @param Locale $locale
     * @param bool $forceCreation
     * @return bool|TranslationInterface
     */
    public function getTranslationByLocale(Locale $locale, $forceCreation = false)
    {
        $translation = $this->getTranslationObjectForLocale($locale);
        if ($translation) {
            return $translation;
        }

        if($forceCreation){
            return $this->createTranslation($locale);
        }

        return null;
    }

    /**
     * Remove a translation object
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
        $translation = $this->getTranslationObjectForLocale($locale);
        if ($translation) {
            $this->translations->removeElement($translation);
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

    /**
     * Return unannotated translatable fields
     *
     * @return array
     */
    public function getTranslatableFields()
    {
        return $this->translatableFields;
    }

    /**
     * Set the translatable fields.
     *
     * @param array $fields The name of the translatable fields
     */
    public function setTranslatableFields($fields)
    {
        $this->translatableFields = $fields;
    }

    /**
     * Set the given locale as the current one. If the locale does not yet exist and $forceCreation is set, then it is
     * automatically created.
     *
     * @param Locale $locale
     * @param bool $forceCreation
     * @return $this
     */
    public function setCurrentLocale(Locale $locale, $forceCreation = false)
    {
        $this->curTranslation = $this->getTranslationObjectForLocale($locale);

        if(false === $this->curTranslation && $forceCreation){
            $this->curTranslation = $this->createTranslation($locale);
        }

        return $this;
    }

    /**
     * Get the locale set as current, if any.
     *
     * @return null|Locale
     */
    public function getCurrentLocale()
    {
        if ($this->curTranslation !== null) {
            return $this->curTranslation->getI18nLocale();
        }

        return null;
    }

    public function resetCurrentLocale()
    {
        $this->curTranslation = null;
    }

    /**
     * Defines the getters and setters for translatable fields, basically delegating the responsibility of the execution
     * to the appropriate translation object.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed|null
     */
    public function __call($method, $arguments)
    {
        // By default we use the locale set with setCurrentLocale
        $translation = $this->curTranslation;

        $lastParam = end($arguments);
        if($lastParam && $lastParam instanceof Locale) {
            // If a locale is passed as last parameter, then use the associated translation
            $translation = $this->getTranslationObjectForLocale($lastParam);
            array_pop($arguments); // Remove the last parameter as we just "consumed" it
        } else {
            // If the last param is a boolean and the previous one is a Locale then use the associated translation
            // forcing its creation if it does not exist
            $previousParam = prev($arguments);
            if (is_bool($lastParam) && $previousParam instanceof Locale) {
                $translation = $this->getTranslationObjectForLocale($previousParam);
                if (!$translation && $lastParam) {
                    $translation = $this->createTranslation($previousParam);
                }
                // Remove the two last parameters as we just "consumed" them
                array_pop($arguments);
                array_pop($arguments);
            }
        }

        if (!$translation) {
            return null;
        }

        return call_user_func_array(array($translation, $method), $arguments);
    }

    /**
     * Go through all the translation objects and return the one that matches the given locale or false if none were found.
     *
     * @param Locale $locale
     * @return bool|TranslationInterface
     */
    protected function getTranslationObjectForLocale(Locale $locale)
    {
        /** @var TranslationInterface $translation */
        foreach ($this->translations as $translation) {
            if ((string)$translation->getI18nLocale() === (string)$locale) {
                $translation->setI18nParent($this, false);
                return $translation;
            }
        }

        return false;
    }

    /**
     * Create a new related translation object for the given locale if it does not already exist, and then return it.
     *
     * @param Locale $locale
     * @return bool|TranslationInterface
     */
    protected function createTranslation(Locale $locale)
    {
        $translation = $this->getTranslationObjectForLocale($locale);
        if (!$translation) {
            $translationClassName = $this->getTranslationClassName();
            $translation = new $translationClassName($locale);
            $this->addTranslation($translation);
        }
        return $translation;
    }
}