<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */
namespace CDSRC\Libraries\Translatable\Property\TypeConverter;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\I18n\Locale;
use Neos\Flow\Property\Exception\InvalidPropertyMappingConfigurationException;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;

/**
 * Converter which transforms Locale/String types to Locale.
 *
 * @Flow\Scope("singleton")
 */
class LocaleTypeConverter extends AbstractTypeConverter
{

    /**
     * {@inheritdoc}
     *
     * @throws InvalidLocaleIdentifierException
     * @throws InvalidPropertyMappingConfigurationException
     */
    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = array(),
        PropertyMappingConfigurationInterface $configuration = null
    ) {
        $locale = $source;
        if (is_string($locale) && strlen($locale) > 0) {
            $locale = new Locale($locale);
        }
        if ($locale instanceof Locale) {
            return $locale;
        }
        throw new InvalidPropertyMappingConfigurationException(
            'Unable to convert given value to Locale object',
            1467326651
        );
    }
}
