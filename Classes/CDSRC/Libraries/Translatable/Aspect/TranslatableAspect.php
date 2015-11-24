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
use Doctrine\ORM\Query;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;
use TYPO3\Flow\Mvc\Controller\Argument;
use TYPO3\Flow\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter;
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
     * @Flow\Before("method(TYPO3\Flow\Mvc\Controller\Argument->setValue())")
     * @param JoinPointInterface $joinPoint The current joinpoint
     *
     * @return void
     */
    public function fixTranslatableArguments(JoinPointInterface $joinPoint)
    {
        /** @var Argument $argument */
        $argument = $joinPoint->getProxy();
        if ($this->reflectionService->isClassImplementationOf($argument->getDataType(), TranslatableInterface::class)) {
            $rawValue = $joinPoint->getMethodArgument('rawValue');
            if (is_array($rawValue) && !isset($rawValue['translations'])) {
                $translationClassName = $this->getTranslationTable($argument->getDataType());
                if ($translationClassName instanceof GenericTranslation) {
                } else {
                    $finalValue = array();
                    $translationProperties = $this->getTranslationProperties($translationClassName);
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
                        } else {
                            $finalValue[$property] = $value;
                        }
                    }
                    if (!empty($translations)) {
                        $identifierPropertyName = $this->getTranslationIdentifierPropertyName($translationClassName);
                        // Retrieve UUID of existing translation if mapping an existing object
                        if (isset($rawValue['__identity']) && $identifierPropertyName) {
                            $translationsData = $this->findTranslationsIdentifiersAndLocale($translationClassName, $rawValue['__identity'], $identifierPropertyName);
                            if(is_array($translationsData)) {
                                foreach ($translationsData as $data) {
                                    if(isset($translations[$data['i18nLocale']])){
                                        $translations[$data['i18nLocale']]['__identity'] = $data['__identity'];
                                    }
                                }
                            }
                        }
                        $index = 0;
                        /** @var MvcPropertyMappingConfiguration $propertyMappingConfiguration */
                        $propertyMappingConfiguration = $argument->getPropertyMappingConfiguration();
                        $propertyMappingConfiguration->allowProperties('translations');
                        $propertyMappingConfiguration->setTargetTypeForSubProperty('translations', 'Doctrine\Common\Collections\Collection<\\'.$translationClassName.'>');
                        foreach ($translations as $language => $translation) {
                            $rawValue['translations'][$index] = $translation;
                            $propertyPath = 'translations.'.$index;
                            $propertyMappingConfiguration->setTargetTypeForSubProperty($propertyPath, $translationClassName);
                            $translationMappingConfiguration = $propertyMappingConfiguration->forProperty($propertyPath);
                            if(isset($translation['__identity'])){
                                $propertyMappingConfiguration->allowModificationForSubProperty($propertyPath);
                            }else{
                                $propertyMappingConfiguration->allowCreationForSubProperty($propertyPath);
                                $translationMappingConfiguration->allowProperties('i18nLocale');
                                $rawValue['translations'][$index]['i18nLocale'] = $language;
                            }
                            call_user_func_array(array($translationMappingConfiguration, 'allowProperties'), $translationProperties);
                            $index++;
                        }
                        call_user_func_array(array($propertyMappingConfiguration->forProperty('translations'), 'allowProperties'), array_keys($rawValue['translations']));
                    }
                }
            }
            $joinPoint->setMethodArgument('rawValue', $rawValue);
        }
    }

    /**
     * @param string $className
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
            $this->translationIdentifierPropertyName[$translationClassName] = isset($propertyNames[0]) ? $propertyNames[0]: null;
        }

        return $this->translationIdentifierPropertyName[$translationClassName];
    }

    /**
     * @param $className
     * @param $parentIdentifier
     * @param string $identifierPropertyName
     *
     * @return mixed
     */
    public function findTranslationsIdentifiersAndLocale($className, $parentIdentifier, $identifierPropertyName = 'Persistence_Object_Identifier'){
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->createQueryBuilder();
        return $queryBuilder
            ->select('t.'.$identifierPropertyName .' AS __identity', 't.i18nLocale')
            ->from($className, 't')
            ->andWhere('t.i18nParent = :identifier')
            ->setParameter('identifier', $parentIdentifier)
            ->getQuery()->execute(null, Query::HYDRATE_ARRAY);
    }
}