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
     * Initialize the arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('items', 'Traversable', 'Translatable item list', true, null);
        $this->registerArgument('property', 'string', 'The property to order by', true, null);
        $this->registerArgument('locale', 'mixed', 'Locale to translate to', true, null);
        $this->registerArgument('alternativeLocale', 'mixed', 'Fallback locale if given property doesn\'t exist on given Locale', false, null);
        $this->registerArgument('as', 'string', '', false, 'items');
    }


    /**
     * @return string
     */
    public function render(): string
    {
        $items = $this->arguments['items'] ?: [];
        $property = $this->arguments['property'];
        $locale = $this->arguments['locale'];
        $alternativeLocale = $this->arguments['alternativeLocale'];
        $as = $this->arguments['as'] ?: "items";

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

        } catch (Exception) {
            return '';
        }
    }

}
