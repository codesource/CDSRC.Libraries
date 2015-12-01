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
use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;
use TYPO3\Flow\Mvc\View\JsonView;

/**
 * An aspect which centralizes the logging of security relevant actions.
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class JsonViewAspect
{

    /**
     *
     * @Flow\Around("within(TYPO3\Flow\Mvc\View\JsonView) && method(.*->transformObject())")
     * @param JoinPointInterface $joinPoint The current joinpoint
     *
     * @return \TYPO3\Flow\Mvc\Controller\Argument
     */
    public function interceptTransformObject(JoinPointInterface $joinPoint)
    {
        $object = $joinPoint->getMethodArgument('object');

        if (!is_object($object) || !$object instanceof AbstractTranslatable) {
            return $joinPoint->getAdviceChain()->proceed($joinPoint);
        }

        $configuration = $joinPoint->getMethodArgument('configuration');

        $defaultExcludedProperties = array(
            'currentLocale',
            'fallbackOnTranslation',
            'translationClassName',
            'translationForLocale'
        );

        if (!isset($configuration['_exclude'])) {
            // Exclude by default translatable gettable property if no _exclude is defined
            $configuration['_exclude'] = $defaultExcludedProperties;
        } elseif (is_array($configuration['_exclude']) && in_array('_translation', $configuration['_exclude'])) {
            // Exclude translatable gettable properties if _translation is present in the excluded properties
            $configuration['_exclude'] = array_merge($configuration['_exclude'], $defaultExcludedProperties);
        }
        // Prevent call of getter with required arguments
        $configuration['_exclude'] = array_merge($configuration['_exclude'], array('translationByLocale'));
        $joinPoint->setMethodArgument('configuration', $configuration);

        $propertiesToRender = $joinPoint->getAdviceChain()->proceed($joinPoint);
        $translatablePropertiesToRender = $this->transformTranslatableObject(
            $object,
            $configuration,
            $joinPoint->getProxy()
        );

        return array_merge($propertiesToRender, $translatablePropertiesToRender);
    }

    /**
     * Traverses the given object structure in order to transform it into an
     * array structure.
     *
     * @param AbstractTranslatable $object Object to traverse
     * @param array $configuration Configuration for transforming the given object or NULL
     * @param \TYPO3\Flow\Mvc\View\JsonView $view
     *
     * @return array Object structure as an array
     * @throws \TYPO3\Flow\Reflection\Exception\PropertyNotAccessibleException
     */
    protected function transformTranslatableObject(AbstractTranslatable $object, array $configuration, JsonView $view)
    {
        $additionalPropertyNames = forward_static_call(array(get_class($object), 'getTranslatableFields'));


        $propertiesToRender = array();
        foreach ($additionalPropertyNames as $propertyName) {
            if (isset($configuration['_only'])
                && is_array($configuration['_only'])
                && !in_array($propertyName, $configuration['_only'])) {
                continue;
            }
            if (isset($configuration['_exclude'])
                && is_array($configuration['_exclude'])
                && in_array($propertyName, $configuration['_exclude'])) {
                continue;
            }
            $getter = 'get' . ucfirst($propertyName);

            $translations = $object->getTranslations();
            if (count($translations) > 0) {
                $propertiesToRender[$propertyName] = array();
                /** @var AbstractTranslation $translation */
                foreach ($translations as $translation) {
                    $propertyValue = $translation->$getter();
                    if (is_array($propertyValue) || is_object($propertyValue)) {
                        $propertyValue = $this->transformValue(
                            $propertyValue,
                            $configuration['_descend'][$propertyName],
                            $view
                        );
                    }
                    $propertiesToRender[$propertyName][(string)$translation->getI18nLocale()] = $propertyValue;
                }
            }
        }

        return $propertiesToRender;
    }

    /**
     * Transforms a value depending on type recursively using the
     * supplied configuration.
     *
     * @param mixed $value The value to transform
     * @param array $configuration Configuration for transforming the value
     * @param \TYPO3\Flow\Mvc\View\JsonView $view
     *
     * @return array The transformed value
     */
    protected function transformValue($value, array $configuration, JsonView $view)
    {
        $reflectionMethod = new \ReflectionMethod(get_class($view), 'transformValue');
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke($view, $value, $configuration);
    }
}