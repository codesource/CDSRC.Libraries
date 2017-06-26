<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */
namespace CDSRC\Libraries\Translatable\ViewHelpers;


use TYPO3\Flow\I18n\Locale;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

class OrderByViewHelper extends AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @param \Traversable $items
     * @param string $property
     * @param string|Locale $locale
     * @param string|Locale|null $alternativeLocale
     * @param string $as
     *
     * @return string
     */
    public function render(\Traversable $items, $property, $locale, $alternativeLocale = null, $as = "items")
    {
        try {
            $sortedArray = [];
            $localeObject = $locale instanceof Locale ? $locale : new Locale($locale);
            $getter = 'get' . ucfirst($property);
            if($alternativeLocale === null) {
                foreach ($items as $index => $item) {
                    $sortedArray[$item->$getter($localeObject) . ':' . $index] = $item;
                }
            }else{
                $alternativeLocaleObject =  $alternativeLocale instanceof Locale ? $alternativeLocale : new Locale($alternativeLocale);
                foreach ($items as $index => $item) {
                    $sortedArray[$item->$getter($localeObject, $alternativeLocaleObject) . ':' . $index] = $item;
                }
            }
            ksort($sortedArray);
            $this->templateVariableContainer->add($as, $sortedArray);
            $output = $this->renderChildren();
            $this->templateVariableContainer->remove($as);

            return $output;

        } catch (\Exception $e) {
            return '';
        }
    }

}