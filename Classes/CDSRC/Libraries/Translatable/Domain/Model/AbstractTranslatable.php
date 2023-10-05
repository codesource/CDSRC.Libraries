<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\I18n\Locale;
use Neos\Flow\ObjectManagement\Exception\InvalidObjectException;
use Neos\Flow\Property\Exception\InvalidPropertyException;
use Neos\Flow\Reflection\Exception\InvalidClassException;

/**
 * Abstract class for translatable entities
 *
 * @Flow\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\Table(name="cdsrc_libraries_trsl_abstracttranslatable")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractTranslatable implements TranslatableInterface
{

    /**
     *
     * @var string|null
     * @Flow\Transient
     */
    protected ?string $translationClassName = null;

    /**
     * Will fallback to default language if no translation found
     *
     * @var bool
     * @Flow\Transient
     */
    protected bool $fallbackOnTranslation = true;

    /**
     * List of translations
     *
     * @var Collection<AbstractTranslation>
     * @ORM\OneToMany(mappedBy="i18nParent", cascade={"all"}, orphanRemoval=true, fetch="LAZY")
     */
    protected Collection $translations;

    /**
     * The current translation object to use (set it using setCurrentLocale)
     *
     * @Flow\Transient
     * @var TranslationInterface|null
     */
    protected ?TranslationInterface $curTranslation = null;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Clone entity
     */
    public function __clone()
    {
        $translations = $this->getTranslations();
        $this->translations = new ArrayCollection();
        /** @var AbstractTranslation $translation */
        foreach ($translations as $translation) {
            $newTranslation = clone $translation;
            $this->translations->add($newTranslation->setI18nParent($this, false));
        }
    }

    /**
     * Get translation class name
     *
     * @return string
     */
    public function getTranslationClassName(): string
    {
        if ($this->translationClassName === null) {
            $specificTranslationClassName = get_called_class() . 'Translation';
            if (class_exists($specificTranslationClassName)) {
                $this->translationClassName = $specificTranslationClassName;
            } else {
                $this->translationClassName = 'CDSRC\Libraries\Translatable\Domain\Model\GenericTranslation';
            }
        }

        return $this->translationClassName;
    }

    /**
     *  Get fallback on translation status
     *
     * @return bool
     */
    public function getFallbackOnTranslation(): bool
    {
        return $this->fallbackOnTranslation;
    }

    /**
     * Set fallback on translation status
     *
     * @param bool $fallback
     *
     * @return AbstractTranslatable
     */
    public function setFallbackOnTranslation(bool $fallback): static
    {
        $this->fallbackOnTranslation = $fallback;

        return $this;
    }

    /**
     * Check if object has a translation for a specific locale
     *
     * @param Locale $locale
     *
     * @return bool
     */
    public function hasTranslationForLocale(Locale $locale): bool
    {
        return $this->getTranslationObjectForLocale($locale) !== null;
    }

    /**
     * Add a new translation to object
     * Notice: Only one translation by locale and object should exist
     *
     * @param TranslationInterface $translation
     *
     * @return AbstractTranslatable
     */
    public function addTranslation(TranslationInterface $translation): static
    {
        try {
            $hasTranslation = $this->hasTranslationForLocale($translation->getI18nLocale());
        } catch (InvalidLocaleIdentifierException) {
            $hasTranslation = false;
        }
        if (!($this->getTranslations()->contains($translation) || $hasTranslation)) {
            $this->getTranslations()->add($translation);
            $translation->setI18nParent($this);
        }

        return $this;
    }

    /**
     * Remove a translation object
     *
     * @param TranslationInterface $translation
     *
     * @return TranslatableInterface
     */
    public function removeTranslation(TranslationInterface $translation): static
    {
        if ($this->getTranslations()->contains($translation)) {
            $this->getTranslations()->removeElement($translation);
        }

        return $this;
    }

    /**
     * Remove a translation by locale
     *
     * @param Locale $locale
     *
     * @return TranslatableInterface
     */
    public function removeTranslationByLocale(Locale $locale): static
    {
        $translation = $this->getTranslationObjectForLocale($locale);
        if ($translation !== null) {
            $this->getTranslations()->removeElement($translation);
        }

        return $this;
    }

    /**
     * Remove all translations
     *
     * @return TranslatableInterface
     */
    public function removeAllTranslations(): static
    {
        foreach ($this->getTranslations() as $translation) {
            $this->removeTranslation($translation);
        }

        return $this;
    }

    /**
     * Get the translation object associated with the given locale. If it does not exist and $forceCreation is set, then
     * the translation object is created.
     *
     * @param Locale $locale
     * @param bool $forceCreation
     *
     * @return TranslationInterface|null
     */
    public function getTranslationByLocale(Locale $locale, bool $forceCreation = false): TranslationInterface|null
    {
        $translation = $this->getTranslationObjectForLocale($locale);
        if ($translation !== null) {
            return $translation;
        }

        if ($forceCreation) {
            return $this->createTranslation($locale);
        }

        return null;
    }

    /**
     * Set the given locale as the current one. If the locale does not yet exist and $forceCreation is set, then it is
     * automatically created.
     *
     * @param Locale $locale
     * @param bool $forceCreation
     *
     * @return AbstractTranslatable
     */
    public function setCurrentLocale(Locale $locale, bool $forceCreation = false): static
    {
        $this->curTranslation = $this->getTranslationObjectForLocale($locale);

        if ($this->curTranslation === null && $forceCreation) {
            $this->curTranslation = $this->createTranslation($locale);
        }

        return $this;
    }

    /**
     * Replace all translations by the given collection
     *
     * @param Collection<TranslationInterface> $translations
     *
     * @return TranslatableInterface
     *
     * @throws InvalidClassException
     * @throws InvalidLocaleIdentifierException
     * @throws InvalidObjectException
     * @throws InvalidPropertyException
     */
    public function setTranslations(Collection $translations) : static
    {
        $translationsToRemove = [];

        /** @var AbstractTranslation $translation */
        foreach ($this->getTranslations() as $translation) {
            /** @var AbstractTranslation $newTranslation */
            foreach ($translations as $newTranslation) {
                if ((string)$newTranslation->getI18nLocale() === (string)$translation->getI18nLocale()) {
                    $translation->mergeWithTranslation($newTranslation);
                    $translations->remove($translations->indexOf($newTranslation));
                    break 2;
                }
            }
            $translationsToRemove[] = $translation;
        }
        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }
        foreach ($translationsToRemove as $translation) {
            $this->removeTranslation($translation);
        }

        return $this;
    }

    /**
     * Get the locale set as current, if any.
     *
     * @return Locale|null
     */
    public function getCurrentLocale(): ?Locale
    {
        try {
            return $this->curTranslation?->getI18nLocale();
        } catch (InvalidLocaleIdentifierException) {
            return null;
        }
    }

    /**
     * Get all translations
     *
     * @return Collection<TranslationInterface>
     */
    public function getTranslations(): Collection
    {
        return $this->translations ?? new ArrayCollection();
    }

    public function resetCurrentLocale(): void
    {
        $this->curTranslation = null;
    }

    /**
     * Return unannotated translatable fields
     * NOTICE: This function should be override in sub classes if needed.
     *
     * @return array
     */
    public static function getTranslatableFields(): array
    {
        return [];
    }

    /**
     * Return the list of all locales that have a translation
     *
     * @return array
     */
    public function getAvailableLocales(): array
    {
        $locales = [];
        foreach ($this->getTranslations() as $translation) {
            try {
                $locales[] = (string)$translation->getI18nLocale();
            } catch (InvalidLocaleIdentifierException) {
            }
        }

        return array_unique($locales);
    }

    /**
     * Defines the getters and setters for translatable fields, basically delegating the responsibility of the execution
     * to the appropriate translation object.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed|null
     * @throws InvalidLocaleIdentifierException
     */
    public function __call(string $method, array $arguments)
    {
        // By default, we use the locale set with setCurrentLocale
        /** @var TranslationInterface $translation */
        $translation = $this->curTranslation;

        $firstParam = reset($arguments);
        $countArgument = count($arguments);
        if ($countArgument === 1 && str_starts_with($method, 'set') && is_array($firstParam)) {

            // If function is a setter and argument is an array of translation, iterate through translation
            foreach ($firstParam as $locale => $value) {
                call_user_func_array(array($this, $method), array($value, new Locale($locale), true));
            }

            return $this;
        } elseif ($countArgument === 0 && str_starts_with($method, 'getAll')) {
            $values = [];
            $method = preg_replace('/^getAll/', 'get', $method);
            foreach ($this->getTranslations() as $translation) {
                $values[(string)$translation->getI18nLocale()] = call_user_func_array(array(
                    $translation,
                    $method,
                ), []);
            }

            return $values;
        } else {
            $lastParam = end($arguments);
            if ($lastParam instanceof Locale) {
                // If a locale is passed as last parameter, then use the associated translation
                $translation = $this->getTranslationObjectForLocale($lastParam);
                array_pop($arguments); // Remove the last parameter as we just "consumed" it

                // If function is a getter, try to see if we have a fallback Locale
                if ($this->getFallbackOnTranslation() && str_starts_with($method, 'get')) {
                    $newLastParam = end($arguments);
                    if ($newLastParam instanceof Locale) {
                        $originalTranslation = $this->getTranslationObjectForLocale($newLastParam);
                        array_pop($arguments); // Remove the last parameter as we just "consumed" it
                        if ($originalTranslation !== null) {
                            $translation = $originalTranslation;
                        }
                    }
                }
            } else {
                // If the last param is a boolean and the previous one is a Locale then use the associated translation
                // forcing its creation if it does not exist
                $previousParam = prev($arguments);
                if (is_bool($lastParam) && $previousParam instanceof Locale) {
                    $translation = $this->getTranslationObjectForLocale($previousParam);
                    if ($translation === null && $lastParam) {
                        $translation = $this->createTranslation($previousParam);
                    }
                    // Remove the two last parameters as we just "consumed" them
                    array_pop($arguments);
                    array_pop($arguments);
                }
            }
        }

        if ($translation === null) {
            return null;
        }

        return call_user_func_array(array($translation, $method), $arguments);
    }

    /**
     * Go through all the translation objects and return the one that matches the given locale or false if none were found.
     *
     * @param Locale $locale
     *
     * @return TranslationInterface|null
     */
    protected function getTranslationObjectForLocale(Locale $locale): ?TranslationInterface
    {
        /** @var TranslationInterface $translation */
        foreach ($this->getTranslations() as $translation) {
            try {
                if ((string)$translation->getI18nLocale() === (string)$locale) {
                    $translation->setI18nParent($this, false);

                    return $translation;
                }
            } catch (InvalidLocaleIdentifierException) {
            }
        }

        return null;
    }

    /**
     * Create a new related translation object for the given locale if it does not already exist, and then return it.
     *
     * @param Locale $locale
     *
     * @return TranslationInterface|null
     */
    protected function createTranslation(Locale $locale): ?TranslationInterface
    {
        $translation = $this->getTranslationObjectForLocale($locale);
        if ($translation === null) {
            $translationClassName = $this->getTranslationClassName();
            $translation = new $translationClassName($locale);
            $this->addTranslation($translation);
        }

        return $translation;
    }


}
