<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Aspect;

use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslatable;
use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation;
use CDSRC\Libraries\Translatable\Domain\Model\GenericTranslation;
use CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface;
use CDSRC\Libraries\Translatable\Property\TypeConverter\LocaleTypeConverter;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Exception;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Error\Messages\Result;
use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\Mvc\Controller\Argument;
use Neos\Flow\Mvc\Controller\MvcPropertyMappingConfiguration;
use Neos\Flow\Reflection\ReflectionService;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Traversable;

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
    protected ReflectionService $reflectionService;

    /**
     * @var EntityManagerInterface
     * @Flow\Inject
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @var array
     */
    protected array $translationClassNames = array();

    /**
     * @var array
     */
    protected array $translationProperties = array();

    /**
     * @var array
     */
    protected array $translationIdentifierPropertyName = array();

    /**
     *
     * @Flow\Around("method(Neos\Flow\Mvc\Controller\Argument->setValue())")
     * @param JoinPointInterface $joinPoint The current joinpoint
     *
     * @return Argument
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function fixTranslatableArguments(JoinPointInterface $joinPoint): Argument
    {
        /** @var Argument $argument */
        $argument = $joinPoint->getProxy();

        if ($this->reflectionService->isClassImplementationOf($argument->getDataType(), TranslatableInterface::class)) {
            // Update method argument
            $this->updateRawValueMethodArgument($joinPoint);

            // Proceed joinPoint
            $joinPoint->getAdviceChain()->proceed($joinPoint);

            // Update validation path
            $this->updateValidationPaths($joinPoint);

            // TODO: UPDATE VALIDATION PATH FOR NESTED PROPERTIES
        } else {
            $joinPoint->getAdviceChain()->proceed($joinPoint);
        }

        return $argument;
    }


    /**
     * Update rawValue argument of "setValue" method
     *
     * @param JoinPointInterface $joinPoint
     *
     * @return array
     * @throws Exception
     */
    protected function updateRawValueMethodArgument(JoinPointInterface $joinPoint): array
    {
        /** @var Argument $argument */
        $argument = $joinPoint->getProxy();
        $rawValue = $joinPoint->getMethodArgument('rawValue');
        $translations = array();
        if (is_array($rawValue) && !isset($rawValue['translations'])) {
            $translations = $this->updateRawValueForEntity(
                $argument->getDataType(),
                $rawValue,
                $argument->getPropertyMappingConfiguration()
            );
            $joinPoint->setMethodArgument('rawValue', $rawValue);
        }

        return $translations;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    protected function getTranslationTable(string $className): string
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
    protected function getTranslationProperties($translationClassName): array
    {
        if (!isset($this->translationProperties[$translationClassName])) {
            $properties = $this->reflectionService->getClassPropertyNames($translationClassName);
            foreach ($properties as $key => $value) {
                $annotations = $this->reflectionService->getPropertyAnnotations($translationClassName, $value);
                if (isset($annotations['CDSRC\Libraries\Translatable\Annotations\Locked']) ||
                    isset($annotations['Doctrine\ORM\Mapping\Id']) ||
                    isset($annotations['Neos\Flow\Annotations\Transient']) ||
                    isset($annotations['Neos\Flow\Annotations\Inject'])
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
    protected function getTranslationsAndUpdateRawValue(array &$rawValue, array $translationProperties): array
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
    protected function setIdentityToExistingTranslation(array &$translations, array $rawValue, string $translationClassName): void
    {
        $identifierPropertyName = $this->getTranslationIdentifierPropertyName($translationClassName);
        if (isset($rawValue['__identity']) && $identifierPropertyName) {
            $translationsData = $this->findTranslationsIdentifiersAndLocale($translationClassName, $rawValue['__identity'], $identifierPropertyName);
            foreach ($translationsData as $data) {
                if (isset($translations[$data['i18nLocale']])) {
                    $translations[$data['i18nLocale']]['__identity'] = $data['__identity'];
                }
            }
        }
    }

    /**
     * Get identifier property name for translation class
     *
     * @param $translationClassName
     *
     * @return string|null
     */
    protected function getTranslationIdentifierPropertyName($translationClassName): ?string
    {
        if (!isset($this->translationIdentifierPropertyName[$translationClassName])) {
            $propertyNames = $this->reflectionService->getPropertyNamesByAnnotation($translationClassName,
                'Doctrine\ORM\Mapping\Id');
            $this->translationIdentifierPropertyName[$translationClassName] = $propertyNames[0] ?? null;
        }

        return $this->translationIdentifierPropertyName[$translationClassName];
    }

    /**
     * @param string $className
     * @param string $parentIdentifier
     * @param string $identifierPropertyName
     *
     * @return array
     */
    protected function findTranslationsIdentifiersAndLocale(
        string $className,
        string $parentIdentifier,
        string $identifierPropertyName = 'Persistence_Object_Identifier'
    ): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        return $queryBuilder
            ->select('t.' . $identifierPropertyName . ' AS __identity', 't.i18nLocale')
            ->from($className, 't')
            ->andWhere('t.i18nParent = :identifier')
            ->setParameter('identifier', $parentIdentifier)
            ->getQuery()->execute(null, AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Inject translations in rawValue and add permission on propertyMappingConfiguration
     *
     * @param array $translations
     * @param array $rawValue
     * @param string $translationClassName
     * @param array $translationProperties
     * @param MvcPropertyMappingConfiguration $propertyMappingConfiguration
     *
     * @return void
     */
    protected function injectTranslationsInRawValue(
        array                           $translations,
        array                           &$rawValue,
        string                          $translationClassName,
        array                           $translationProperties,
        MvcPropertyMappingConfiguration $propertyMappingConfiguration
    ): void
    {
        $index = 0;
        $propertyMappingConfiguration->allowProperties('translations');
        $propertyMappingConfiguration->setTargetTypeForSubProperty('translations',
            'Doctrine\Common\Collections\Collection<\\' . $translationClassName . '>');
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
                $propertyMappingConfiguration->forProperty($propertyPath . '.i18nLocale')->setTypeConverter($localeTypeConverter);
                $rawValue['translations'][$index]['i18nLocale'] = $language;
            }

            // Allow all translation properties
            call_user_func_array(array($translationMappingConfiguration, 'allowProperties'), $translationProperties);
            $index++;
        }

        // Allow all translation indexes
        call_user_func_array(array($propertyMappingConfiguration->forProperty('translations'), 'allowProperties'),
            array_keys($rawValue['translations']));
    }

    /**
     * Update validation result paths for arguments
     *
     * @param JoinPointInterface $joinPoint
     *
     * @return void
     *
     * @throws InvalidLocaleIdentifierException
     * @throws ReflectionException
     */
    protected function updateValidationPaths(JoinPointInterface $joinPoint): void
    {
        /** @var Argument $argument */
        $argument = $joinPoint->getProxy();
        /** @var Result $argumentValidationResults */
        $argumentValidationResults = $argument->getValidationResults();

        $this->mergeTranslationValidationResults($argumentValidationResults, $argument->getValue());
    }

    /**
     * @param Result $validationResults
     * @param AbstractTranslatable|null $translatableObject
     *
     * @throws ReflectionException
     * @throws InvalidLocaleIdentifierException
     */
    protected function mergeTranslationValidationResults(Result $validationResults, AbstractTranslatable $translatableObject = null): void
    {
        if ($translatableObject) {
            $translationsValidationResults = $validationResults->forProperty('translations');
            if ($translationsValidationResults->hasMessages()) {
                $flattenedErrors = $this->sanitizeValidationResultsPropertyKeys($translationsValidationResults->getFlattenedErrors());
                $flattenedNotices = $this->sanitizeValidationResultsPropertyKeys($translationsValidationResults->getFlattenedNotices());
                $flattenedWarnings = $this->sanitizeValidationResultsPropertyKeys($translationsValidationResults->getFlattenedWarnings());
                $this->overrideTranslationsArgumentValidationResults($validationResults);

                $translatableProperties = call_user_func([get_class($translatableObject), 'getTranslatableFields']);
                /** @var AbstractTranslation $translation */
                foreach ($translatableObject->getTranslations() as $index => $translation) {
                    $language = (string)$translation->getI18nLocale();
                    foreach ($translatableProperties as $property) {
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
                            $validationResults->forProperty($property . '.' . $language)->merge($result);
                        }
                    }
                }
            }
            $reflectionClass = new ReflectionClass($translatableObject);
            foreach ($validationResults->getSubResults() as $property => $result) {
                if ($property !== 'translations' && $reflectionClass->hasProperty($property)) {
                    $reflectionProperty = $reflectionClass->getProperty($property);
                    $nestedTranslatableObject = $reflectionProperty->getValue($translatableObject);
                    if ($nestedTranslatableObject instanceof AbstractTranslatable) {
                        $this->mergeTranslationValidationResults($result, $nestedTranslatableObject);
                    } elseif ($nestedTranslatableObject instanceof Traversable) {
                        $index = 0;
                        foreach ($nestedTranslatableObject as $item) {
                            if ($item instanceof AbstractTranslatable) {
                                $subResult = $result->forProperty($index);
                                $this->mergeTranslationValidationResults($subResult, $item);
                            }
                            $index++;
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
    protected function sanitizeValidationResultsPropertyKeys(array $flattenedMessages): array
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
     *
     */
    protected function overrideTranslationsArgumentValidationResults(Result $argumentValidationResults): void
    {
        $validationResultsProperty = new ReflectionProperty(Result::class, 'propertyResults');
        $propertyResults = $validationResultsProperty->getValue($argumentValidationResults);
        if (isset($propertyResults['translations'])) {
            unset($propertyResults['translations']);
        }
        $validationResultsProperty->setValue($argumentValidationResults, $propertyResults);

    }

    /**
     * @param string $className
     * @param array $rawValue
     * @param MvcPropertyMappingConfiguration $propertyMappingConfiguration
     *
     * @return array
     *
     * @throws Exception
     */
    protected function updateRawValueForEntity(string $className, array &$rawValue, MvcPropertyMappingConfiguration $propertyMappingConfiguration): array
    {
        $translationClassName = $this->getTranslationTable($className);
        if (is_a($translationClassName, GenericTranslation::class)) {
            // TODO: Implement generic version
            throw new Exception('NOT IMPLEMENTED');
        } else {
            $translationProperties = $this->getTranslationProperties($translationClassName);
            $translations = $this->getTranslationsAndUpdateRawValue($rawValue, $translationProperties);
            if (!empty($translations)) {
                $this->setIdentityToExistingTranslation($translations, $rawValue, $translationClassName);
                $this->injectTranslationsInRawValue(
                    $translations,
                    $rawValue,
                    $translationClassName,
                    $translationProperties,
                    $propertyMappingConfiguration
                );
            }
            $this->updateRawValueNestedEntities($className, $rawValue, $propertyMappingConfiguration);

            return $translations;
        }

    }

    /**
     * @param string $className
     * @param array $rawValue
     * @param MvcPropertyMappingConfiguration $propertyMappingConfiguration
     *
     * @throws Exception
     */
    protected function updateRawValueNestedEntities(string $className, array &$rawValue, MvcPropertyMappingConfiguration $propertyMappingConfiguration): void
    {
        $class = $this->entityManager->getClassMetadata($className);
        foreach ($class->associationMappings as $field => $association) {
            if ($field !== 'translations' && isset($rawValue[$field])) {
                if (is_array($rawValue[$field])) {
                    if ($association['type'] & ClassMetadataInfo::TO_MANY) {
                        foreach ($rawValue[$field] as $key => &$value) {
                            if (is_array($value)) {
                                /** @var MvcPropertyMappingConfiguration $configuration */
                                $configuration = $propertyMappingConfiguration->forProperty($field . '.' . $key);
                                $this->updateRawValueForEntity(
                                    $association['targetEntity'],
                                    $value,
                                    $configuration
                                );
                            }
                        }
                    } else {
                        /** @var MvcPropertyMappingConfiguration $configuration */
                        $configuration = $propertyMappingConfiguration->forProperty($field);
                        $this->updateRawValueForEntity(
                            $association['targetEntity'],
                            $rawValue[$field],
                            $configuration
                        );
                    }
                }
            }
        }
    }
}
