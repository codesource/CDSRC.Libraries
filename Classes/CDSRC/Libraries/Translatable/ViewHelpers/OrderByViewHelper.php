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
     * @param string $as
     *
     * @return string
     */
    public function render(\Traversable $items, $property, $locale, $as = "items")
    {
        try {
            $sortedArray = [];
            $localeObject = $locale instanceof Locale ? $locale : new Locale($locale);
            $getter = 'get' . ucfirst($property);
            foreach ($items as $index => $item) {
                $sortedArray[$item->$getter($localeObject) . ':' . $index] = $item;
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