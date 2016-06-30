<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */
namespace CDSRC\Libraries\Translatable\Property\TypeConverter;


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\I18n\Locale;
use TYPO3\Flow\Property\Exception\InvalidPropertyMappingConfigurationException;
use TYPO3\Flow\Property\PropertyMappingConfigurationInterface;
use TYPO3\Flow\Property\TypeConverter\AbstractTypeConverter;

/**
 * Converter which transforms Locale/String types to Locale.
 *
 * @Flow\Scope("singleton")
 */
class LocaleTypeConverter extends AbstractTypeConverter
{

    /**
     * {@inheritdoc}
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
        if (is_object($locale) && $locale instanceof Locale) {
            return $locale;
        }
        throw new InvalidPropertyMappingConfigurationException(
            'Unable to convert given value to Locale object',
            1467326651
        );
    }
}