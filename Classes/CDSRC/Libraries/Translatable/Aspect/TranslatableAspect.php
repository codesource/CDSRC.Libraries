<?php

namespace CDSRC\Libraries\Translatable\Aspect;


/* **********************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 * ******************************************************************** */

use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslatable;
use CDSRC\Libraries\Translatable\Domain\Model\GenericTranslation;
use CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface;
use CDSRC\Libraries\Translatable\Property\TypeConverter\LocaleTypeConverter;
use Doctrine\ORM\Query;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;
use TYPO3\Flow\Error\Result;
use TYPO3\Flow\I18n\Locale;
use TYPO3\Flow\Mvc\Controller\Argument;
use TYPO3\Flow\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\Flow\Property\TypeConverterInterface;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * An aspect which centralizes the logging of security relevant actions.
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class TranslatableAspect
{

    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     * @Flow\Inject
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $translationClassNames = array();

    /**
     * @var array
     */
    protected $translationProperties = array();

    /**
     * @var array
     */
    protected $translationIdentifierPropertyName = array();

    /**
     *
     * @Flow\Around("method(TYPO3\Flow\Mvc\Controller\Argument->setValue())")
     * @param JoinPointInterface $joinPoint The current joinpoint
     *
     * @return \TYPO3\Flow\Mvc\Controller\Argument
     */
    public function fixTranslatableArguments(JoinPointInterface $joinPoint)
    {
        /** @var Argument $argument */
        $argument = $joinPoint->getProxy();

        if ($this->reflectionService->isClassImplementationOf($argument->getDataType(), TranslatableInterface::class)) {
            // Update method argument
            $translations = $this->updateRawValueMethodArgument($joinPoint);

            // Proceed joinPoint
            $joinPoint->getAdviceChain()->proceed($joinPoint);

            // Update validation path
            $this->updateValidationPaths($joinPoint, $translations);
        } else {
            $joinPoint->getAdviceChain()->proceed($joinPoint);
        }

        return $argument;
    }


    /**
     * Update rawValue argument of "setValue" method
     *
     * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
     *
     * @return array
     * @throws
     */
    protected function updateRawValueMethodArgument(JoinPointInterface &$joinPoint)
    {
        /** @var Argument $argument */
        $argument = $joinPoint->getProxy();
        $rawValue = $joinPoint->getMethodArgument('rawValue');
        $translations = array();
        if (is_array($rawValue) && !isset($rawValue['translations'])) {
            $translationClassName = $this->getTranslationTable($argument->getDataType());
            if ($translationClassName instanceof GenericTranslation) {
                // TODO: Implement generic version
                throw \Exception('NOT IMPLEMENTED');
            } else {
                $translationProperties = $this->getTranslationProperties($translationClassName);
                $translations = $this->getTranslationsAndUpdateRawValue($rawValue, $translationProperties);
                if (!empty($translations)) {
                    $this->setIdentityToExistingTranslation($translations, $rawValue, $translationClassName);
                    $propertyMappingConfiguration = $argument->getPropertyMappingConfiguration();
                    $this->injectTranslationsInRawValue($translations, $rawValue, $translationClassName, $translationProperties, $propertyMappingConfiguration);
                }
            }
            $joinPoint->setMethodArgument('rawValue', $rawValue);
        }

        return $translations;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    protected function getTranslationTable($className)
    {
        if (!isset($this->translationClassNames[$className])) {
            $specificTranslationClassName = $className . 'Translation';
            if (class_exists($specificTranslationClassName)) {
                $this->translationClassNames[$className] = $specificTranslationClassName;
            } else {
                $this->translationClassNames[$className] = 'CDSRC\Libraries\Translatable\Domain\Model\GenericTranslation';
            }
        }

        return $this->translationClassNames[$className];
    }

    /**
     * Get available properties for translation class
     *
     * @param $translationClassName
     *
     * @return array
     */
    protected function getTranslationProperties($translationClassName)
    {
        if (!isset($this->translationProperties[$translationClassName])) {
            $properties = $this->reflectionService->getClassPropertyNames($translationClassName);
            foreach ($properties as $key => $value) {
                $annotations = $this->reflectionService->getPropertyAnnotations($translationClassName, $value);
                if (isset($annotations['CDSRC\Libraries\Translatable\Annotations\Locked']) ||
                    isset($annotations['Doctrine\ORM\Mapping\Id']) ||
                    isset($annotations['TYPO3\Flow\Annotations\Transient']) ||
                    isset($annotations['TYPO3\Flow\Annotations\Inject'])
                ) {
                    unset($properties[$key]);
                }
            }
            $this->translationProperties[$translationClassName] = array_values($properties);
        }

        return $this->translationProperties[$translationClassName];
    }

    /**
     * Get translations array from rowValue and update it
     *
     * @param array $rawValue
     * @param array $translationProperties
     *
     * @return array
     */
    protected function getTranslationsAndUpdateRawValue(array &$rawValue, array $translationProperties)
    {
        $translations = array();
        foreach ($rawValue as $property => $value) {
            if (is_array($value) && in_array($property, $translationProperties)) {
                $defaultLanguageValue = null;
                foreach ($value as $language => $languageValue) {
                    // "default" language key is used to set default language in main class
                    if ($language === 'default') {
                        $defaultLanguageValue = $languageValue;
                    } else {
                        $translations[$language][$property] = $languageValue;
                    }
                }
                if ($defaultLanguageValue !== null) {
                    $rawValue[$property] = $defaultLanguageValue;
                } else {
                    unset($rawValue[$property]);
                }
            }
        }

        return $translations;
    }

    /**
     * Search for translation identifier and set __identity parameter
     *
     * @param array $translations
     * @param array $rawValue
     * @param string $translationClassName
     *
     * @return void
     */
    protected function setIdentityToExistingTranslation(array &$translations, array $rawValue, $translationClassName)
    {
        $identifierPropertyName = $this->getTranslationIdentifierPropertyName($translationClassName);
        if (isset($rawValue['__identity']) && $identifierPropertyName) {
            $translationsData = $this->findTranslationsIdentifiersAndLocale($translationClassName, $rawValue['__identity'], $identifierPropertyName);
            if (is_array($translationsData)) {
                foreach ($translationsData as $data) {
                    if (isset($translations[$data['i18nLocale']])) {
                        $translations[$data['i18nLocale']]['__identity'] = $data['__identity'];
                    }
                }
            }
        }
    }

    /**
     * Get identifier property name for translation class
     *
     * @param $translationClassName
     *
     * @return string
     */
    protected function getTranslationIdentifierPropertyName($translationClassName)
    {
        if (!isset($this->translationIdentifierPropertyName[$translationClassName])) {
            $propertyNames = $this->reflectionService->getPropertyNamesByAnnotation($translationClassName, 'Doctrine\ORM\Mapping\Id');
            $this->translationIdentifierPropertyName[$translationClassName] = isset($propertyNames[0]) ? $propertyNames[0] : null;
        }

        return $this->translationIdentifierPropertyName[$translationClassName];
    }

    /**
     * @param $className
     * @param $parentIdentifier
     * @param string $identifierPropertyName
     *
     * @return array
     */
    protected function findTranslationsIdentifiersAndLocale($className, $parentIdentifier, $identifierPropertyName = 'Persistence_Object_Identifier')
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->createQueryBuilder();

        return $queryBuilder
            ->select('t.' . $identifierPropertyName . ' AS __identity', 't.i18nLocale')
            ->from($className, 't')
            ->andWhere('t.i18nParent = :identifier')
            ->setParameter('identifier', $parentIdentifier)
            ->getQuery()->execute(null, Query::HYDRATE_ARRAY);
    }

    /**
     * Inject translations in rawValue and add permission on propertyMappingConfiguration
     *
     * @param array $translations
     * @param array $rawValue
     * @param string $translationClassName
     * @param array $translationProperties
     * @param \TYPO3\Flow\Mvc\Controller\MvcPropertyMappingConfiguration $propertyMappingConfiguration
     *
     * @return void
     */
    protected function injectTranslationsInRawValue(array $translations, array &$rawValue, $translationClassName, array $translationProperties, MvcPropertyMappingConfiguration $propertyMappingConfiguration)
    {
        $index = 0;
        $propertyMappingConfiguration->allowProperties('translations');
        $propertyMappingConfiguration->setTargetTypeForSubProperty('translations', 'Doctrine\Common\Collections\Collection<\\' . $translationClassName . '>');
        $localeTypeConverter = new LocaleTypeConverter();
        foreach ($translations as $language => $translation) {
            $rawValue['translations'][$index] = $translation;
            $propertyPath = 'translations.' . $index;
            $propertyMappingConfiguration->setTargetTypeForSubProperty($propertyPath, $translationClassName);
            $translationMappingConfiguration = $propertyMappingConfiguration->forProperty($propertyPath);

            // Allow modification if translation exists, else allow creation
            if (isset($translation['__identity'])) {
                $propertyMappingConfiguration->allowModificationForSubProperty($propertyPath);
            } else {
                $propertyMappingConfiguration->allowCreationForSubProperty($propertyPath);
                $translationMappingConfiguration->allowProperties('i18nLocale');
                $propertyMappingConfiguration->forProperty($propertyPath.'.i18nLocale')->setTypeConverter($localeTypeConverter);
                $rawValue['translations'][$index]['i18nLocale'] = $language;
            }

            // Allow all translation properties
            call_user_func_array(array($translationMappingConfiguration, 'allowProperties'), $translationProperties);
            $index++;
        }

        // Allow all translation indexes
        call_user_func_array(array($propertyMappingConfiguration->forProperty('translations'), 'allowProperties'), array_keys($rawValue['translations']));
    }

    /**
     * Update validation result paths for arguments
     *
     * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
     * @param array $translations
     *
     * @return void
     */
    protected function updateValidationPaths(JoinPointInterface &$joinPoint, array $translations)
    {
        /** @var Argument $argument */
        $argument = $joinPoint->getProxy();
        /** @var Result $argumentValidationResults */
        $argumentValidationResults = $argument->getValidationResults();

        $translationsValidationResults = $argumentValidationResults->forProperty('translations');
        if ($translationsValidationResults->hasMessages()) {
            $flattenedErrors = $this->sanitizeValidationResultsPropertyKeys($translationsValidationResults->getFlattenedErrors());
            $flattenedNotices = $this->sanitizeValidationResultsPropertyKeys($translationsValidationResults->getFlattenedNotices());
            $flattenedWarnings = $this->sanitizeValidationResultsPropertyKeys($translationsValidationResults->getFlattenedWarnings());
            $this->overrideTranslationsArgumentValidationResults($argumentValidationResults);

            /** @var AbstractTranslatable $translatableObject */
            $translatableObject = $argument->getValue();
            foreach ($translations as $language => $translation) {
                $translationObject = $translatableObject->getTranslationByLocale(new Locale($language));
                if ($translatableObject !== null) {
                    $index = $translatableObject->getTranslations()->indexOf($translationObject);
                    foreach ($translation as $property => $value) {
                        $propertyPath = $index . '.' . $property;
                        $result = new Result();
                        if (isset($flattenedErrors[$propertyPath]) && is_array($flattenedErrors[$propertyPath])) {
                            foreach ($flattenedErrors[$propertyPath] as $error) {
                                $result->addError($error);
                            }
                        }
                        if (isset($flattenedNotices[$propertyPath]) && is_array($flattenedNotices[$propertyPath])) {
                            foreach ($flattenedNotices[$propertyPath] as $notice) {
                                $result->addNotice($notice);
                            }
                        }
                        if (isset($flattenedWarnings[$propertyPath]) && is_array($flattenedWarnings[$propertyPath])) {
                            foreach ($flattenedWarnings[$propertyPath] as $warning) {
                                $result->addWarning($warning);
                            }
                        }
                        if ($result->hasMessages()) {
                            $argumentValidationResults->forProperty($property . '.' . $language)->merge($result);
                        }
                    }
                }
            }
        }
    }

    /**
     * Sanitize validation results and search for sub validations results
     *
     * @param array $flattenedMessages
     *
     * @return array
     */
    protected function sanitizeValidationResultsPropertyKeys(array $flattenedMessages)
    {
        $sanitizedFlattenedMessages = array();
        foreach ($flattenedMessages as $key => $message) {
            if (($index = strrpos($key, '.translations.')) !== false) {
                $key = substr($key, $index + 14);
            }
            $sanitizedFlattenedMessages[$key] = $message;
        }

        return $sanitizedFlattenedMessages;
    }

    /**
     * Override existing translations argument validation results
     *
     * @param Result $argumentValidationResults
     *
     * @return void
     */
    protected function overrideTranslationsArgumentValidationResults(Result $argumentValidationResults)
    {
        $validationResultsProperty = new \ReflectionProperty(Result::class, 'propertyResults');
        $validationResultsProperty->setAccessible(true);
        $propertyResults = $validationResultsProperty->getValue($argumentValidationResults);
        if (isset($propertyResults['translations'])) {
            unset($propertyResults['translations']);
        }
        $validationResultsProperty->setValue($argumentValidationResults, $propertyResults);

    }
}