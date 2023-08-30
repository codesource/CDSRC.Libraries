<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */
namespace CDSRC\Libraries\Translatable\ViewHelpers;


use Exception;
use Neos\Flow\I18n\Locale;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Traversable;

class OrderByViewHelper extends AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @param Traversable $items
     * @param string $property
     * @param string|Locale $locale
     * @param string|Locale|null $alternativeLocale
     * @param string $as
     *
     * @return string
     */
    public function render(Traversable $items, string $property, string|Locale $locale, string|Locale|null $alternativeLocale = null, string $as = "items"): string
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

        } catch (Exception $e) {
            return '';
        }
    }

}
