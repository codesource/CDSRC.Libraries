<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Aspect;

use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslatable;
use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Property\Exception\InvalidPropertyException;
use Neos\Flow\Reflection\Exception\InvalidClassException;
use ReflectionException;

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
     * @Flow\Around("within(Neos\Flow\Mvc\View\JsonView) && method(.*->transformObject())")
     * @param JoinPointInterface $joinPoint The current joinpoint
     *
     * @return \Neos\Flow\Mvc\Controller\Argument
     *
     * @throws InvalidClassException
     * @throws InvalidPropertyException
     * @throws ReflectionException
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
        /** @var JsonView $view */
        $view = $joinPoint->getProxy();
        $translatablePropertiesToRender = $this->transformTranslatableObject(
            $object,
            $configuration,
            $view
        );

        return array_merge($propertiesToRender, $translatablePropertiesToRender);
    }

    /**
     * Traverses the given object structure in order to transform it into an
     * array structure.
     *
     * @param AbstractTranslatable $object Object to traverse
     * @param array $configuration Configuration for transforming the given object or NULL
     * @param \Neos\Flow\Mvc\View\JsonView $view
     *
     * @return array Object structure as an array
     *
     * @throws InvalidPropertyException
     * @throws InvalidClassException
     * @throws ReflectionException
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
     * @param \Neos\Flow\Mvc\View\JsonView $view
     *
     * @return array The transformed value
     *
     * @throws ReflectionException
     */
    protected function transformValue($value, array $configuration, JsonView $view)
    {
        $reflectionMethod = new \ReflectionMethod(get_class($view), 'transformValue');
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke($view, $value, $configuration);
    }
}