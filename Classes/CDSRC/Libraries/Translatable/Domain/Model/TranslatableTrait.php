<?php

namespace CDSRC\Libraries\Translatable\Domain\Model;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * 
 */
trait TranslatableTrait {

    /**
     * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
     * @Flow\Inject
     * @Flow\Transient
     */
    protected $persistenceManagerForTranslation;

    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     * @Flow\Inject
     * @Flow\Transient
     */
    protected $reflectionServiceForTranslation;

    /**
     * Notice: This property is build on contructor
     * you fix the value by affecting a default value here.
     *
     * @var string
     * @Flow\Transient
     */
    protected $translationClassName;

    /**
     * Current object's locale state
     * 
     * @var string
     * @Flow\Transient
     */
    protected $localeForTranslation;

    /**
     * Will fallback to default language if no translation found
     * 
     * @var boolean
     * @Flow\Transient
     */
    protected $fallbackOnTranslation;

    /**
     * @var array
     * @Flow\Transient
     */
    protected $translationsForTranslation;

    /**
     * Is this object initialized for translation?
     * @var boolean 
     * @Flow\Transient
     */
    private $initializedForTranslation;

    /**
     * Get object's locale state
     * 
     * @return string
     */
    public function getLocaleForTranslation() {
        return $this->localeForTranslation;
    }

    /**a
     * Set object's locale state
     * @param string $locale
     * @return self
     */
    public function setLocaleForTranslation($locale) {
        $this->initializeForTranslation();
        $this->localeForTranslation = $locale;
        return $this;
    }
    
    /**
     * Set object's locale to default state
     * 
     * @return self
     */
    public function setDefaultLocaleForTranslation(){
        $this->initializeForTranslation();
        $this->localeForTranslation = NULL;
        return $this;
    }

    /**
     * Get fallback on translation status
     */
    public function getFallbackOnTranslation() {
        $this->initializeForTranslation();
        $this->fallbackOnTranslation;
    }

    /**
     * Set fallback on translation status
     * @param boolean $fallback
     */
    public function setFallbackOnTranslation($fallback) {
        $this->initializeForTranslation();
        $this->fallbackOnTranslation = $fallback;
    }
    
    /**
     * Load existing translations
     * 
     * @return self
     */
    public function loadTranslations(){
        $this->getTranslations(TRUE);
        return $this;
    }
    
    /**
     * Get all existing translations
     * 
     * @param boolean $forceReload
     * @return array
     */
    public function getTranslations($forceReload = FALSE) {
        $this->initializeForTranslation();
        if ($forceReload) {
            $reference = $this->persistenceManagerForTranslation->getIdentifierByObject($this);
            if ($reference !== NULL) {
                $query = $this->persistenceManagerForTranslation->createQueryForType($this->translationClassName);
                $translations = $query->matching(
                        $query->logicalAnd(
                                $query->equals('referenceToObject', $reference, TRUE),
                                $query->equals('classnameOfObject', get_class($this), TRUE)
                            )
                        )->execute(FALSE);
                if ($translations) {
                    foreach ($translations as $translation) {
                        if (!isset($this->translationsForTranslation[$translation->getCurrentLocale()])) {
                            $this->translationsForTranslation[$translation->getCurrentLocale()] = $translation;
                        }
                    }
                }
            }
        }
        return is_array($this->translationsForTranslation) ? $this->translationsForTranslation : array();
    }

    /**
     * Return translation classname
     * 
     * @return string
     */
    public function getTranslationClass() {
        $this->initializeForTranslation();
        return $this->translationClassName;
    }
    /**
     * Check if object has a translation for a specific locale
     * 
     * @param string $locale
     */
    public function isTranslatable($locale){
        $_locale = trim($locale);
        if(strlen($_locale) === 0){
            return TRUE;
        }
        $translation = $this->getTranslation(FALSE, $_locale);
        return $translation !== NULL;
    }
    
    /**
     * Make sure that translations are all loaded before doing a clone
     * @see #getTranslations(boolean)
     */
    public function __clone(){
        $this->cloneTranslations();
    }
    
    /**
     * Remove a specific translation
     * 
     * @param string $locale
     */
    public function removeTranslation($locale){
        $this->initializeForTranslation();
        $translation = $this->getTranslation(FALSE, $locale);
        if($translation){
            if(isset($this->translationsForTranslation[$locale])){
                unset($this->translationsForTranslation[$locale]);
            }
            $this->persistenceManagerForTranslation->remove($translation);
        }
    }
    
    /**
     * Remove all translations
     * 
     * @param string $locale
     */
    public function removeAllTranslations(){
        $this->initializeForTranslation();
        $translations = $this->getTranslations(TRUE);
        $this->translationsForTranslation = array();
        if(count($translations) > 0){
            foreach($translations as $translation){
                $this->persistenceManagerForTranslation->remove($translation);
            }
        }
    }

    /**
     * Generic translation getter
     * 
     * @param type $property
     * @return type
     * @throws \TYPO3\Flow\Property\Exception\InvalidPropertyException
     */
    protected function getPropertyTranslation($property) {
        $this->initializeForTranslation();
        $annotation = $this->reflectionServiceForTranslation->getPropertyAnnotation(get_class($this), $property, 'CDSRC\\Libraries\\Translatable\\Annotations\\Translatable');
        if (!$annotation instanceof \CDSRC\Libraries\Translatable\Annotations\Translatable) {
            throw new \TYPO3\Flow\Property\Exception\InvalidPropertyException($property . ' is not translatable.', 1428267440);
        }
        if (strlen($this->localeForTranslation) === 0) {
            return $this->$property;
        } else {
            $translation = $this->getTranslation(FALSE);
            if ($translation !== NULL) {
                $getter = 'get' . ucfirst($property);
                return $translation->$getter();
            } elseif ($this->fallbackOnTranslation) {
                return $this->$property;
            }
        }
        return NULL;
    }

    /**
     * Generic translation setter
     * 
     * @param string $property
     * @param mixed $value
     * @return self
     * @throws \TYPO3\Flow\Property\Exception\InvalidPropertyException
     */
    protected function setPropertyTranslation($property, $value) {
        $this->initializeForTranslation();
        $annotation = $this->reflectionServiceForTranslation->getPropertyAnnotation(get_class($this), $property, 'CDSRC\\Libraries\\Translatable\\Annotations\\Translatable');
        if (!$annotation instanceof \CDSRC\Libraries\Translatable\Annotations\Translatable) {
            throw new \TYPO3\Flow\Property\Exception\InvalidPropertyException($property . ' is not translatable.', 1428267440);
        }
        if (strlen($this->localeForTranslation) === 0) {
            $this->$property = $value;
        } else {
            $translation = $this->getTranslation(TRUE);
            $setter = 'set' . ucfirst($property);
            $translation->$setter($value);
        }
        return $this;
    }

    /**
     * Get translation current locale of this object
     * 
     * @param boolean $addTranslationIfNotFound 
     * @return \CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation
     */
    protected function getTranslation($addTranslationIfNotFound = TRUE, $locale = NULL) {
        $this->initializeForTranslation();
        $_locale = $locale !== NULL ? trim($locale) : $this->localeForTranslation;
        if (strlen($_locale) > 0) {
            if (is_array($this->translationsForTranslation) && isset($this->translationsForTranslation[$_locale])) {
                return $this->translationsForTranslation[$_locale];
            }

            $translation = NULL;
            $reference = $this->persistenceManagerForTranslation->getIdentifierByObject($this);
            if ($reference !== NULL) {
                $query = $this->persistenceManagerForTranslation->createQueryForType($this->translationClassName);
                $translation = $query->matching(
                        $query->logicalAnd(
                                $query->equals('referenceToObject', $reference, TRUE),
                                $query->equals('classnameOfObject', get_class($this), TRUE),
                                $query->equals('currentLocale', $_locale, TRUE)
                            )
                        )->execute(FALSE)->getFirst();
            }
            if ($translation !== NULL && $translation) {
                $this->translationsForTranslation[$_locale] = $translation;
                return $translation;
            } elseif ($addTranslationIfNotFound) {
                $className = $this->translationClassName;
                $translation = new $className(get_class($this), $_locale);
                $translation->setReference($reference);
                $this->translationsForTranslation[$_locale] = $translation;
                return $translation;
            }
        }
        return NULL;
    }
    
    /**
     * This function clone all loaded translations
     */
    protected function cloneTranslations(){
        if(is_array($this->translationsForTranslation)){
            $reference = $this->persistenceManagerForTranslation->getIdentifierByObject($this);
            if($reference !== NULL){
                $translations = $this->translationsForTranslation;
                $this->translationsForTranslation = array();
                foreach($translations as $locale => $translation){
                    $this->translationsForTranslation[$locale] = $translation->cloneObject($reference);
                }
            }
        }
    }

    /**
     * Initialize this object
     * Notice: This method must be called in contructor
     * 
     */
    private function initializeForTranslation() {
        if (!$this->initializedForTranslation) {
            $this->translationsForTranslation = array();
            $this->fallbackOnTranslation = TRUE;

            $specificClass = TRUE;
            if (strlen($this->translationClassName) === 0) {
                $this->translationClassName = get_class($this) . 'Translation';
                if (!class_exists($this->translationClassName)) {
                    $this->translationClassName = 'CDSRC\\Libraries\\Translatable\\Domain\\Model\\GenericTranslation';
                    $specificClass = FALSE;
                }
            }
            if ($specificClass) {
                if (class_exists($this->translationClassName)) {
                    if (!is_subclass_of($this->translationClassName, 'CDSRC\\Libraries\\Translatable\\Domain\\Model\\AbstractTranslation')) {
                        throw new \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException($this->translationClassName . ' class doesn\'t extends CDSRC\\Libraries\\Translatable\\Domain\\Model\\AbstractTranslation', 1428240545);
                    }
                } else {
                    throw new \TYPO3\Flow\Persistence\Exception\UnknownObjectException('No translation class found', 1428240546);
                }
            }
            $this->initializedForTranslation = TRUE;
        }
    }

}
