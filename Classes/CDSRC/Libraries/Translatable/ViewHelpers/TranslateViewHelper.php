<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\ViewHelpers;

use CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface;
use Exception;
use Neos\Flow\I18n\Locale;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;

/**
 * Class AssetsViewHelper
 */
class TranslateViewHelper extends AbstractViewHelper
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
        $this->registerArgument('property', 'string', 'Property of the object to be translated', true, null);
        $this->registerArgument('locale', 'mixed', 'Locale to translate to', true, null);
        $this->registerArgument('alternativeLocale', 'mixed', 'Fallback locale if given property doesn\'t exist on given Locale', false, null);
        $this->registerArgument('object', 'CDSRC\\Libraries\\Translatable\\Domain\\Model\\TranslatableInterface', 'The object to be translated', false, null);
    }


    /**
     * Store an asset or render stored assets
     *
     * @return string
     *
     */
    public function render(): string
    {
        $property = $this->arguments['property'] ?: '';
        $locale = $this->arguments['locale'] ?: '';
        $alternativeLocale = $this->arguments['alternativeLocale'];
        $object = $this->arguments['object'];

        if(!$property){
            return '';
        }
        if(empty($object)){
            $object = $this->renderChildren();
        }
        if(!is_object($object) || ! $object instanceof TranslatableInterface){
            return '';
        }
        try {

            $localeObject = $locale instanceof Locale ? $locale : new Locale($locale);
            $getter = 'get' .ucfirst($property);
            if($alternativeLocale === null) {
                return $object->$getter($localeObject) ?? '';
            }else{
                $alternativeLocaleObject =  $alternativeLocale instanceof Locale ? $alternativeLocale : new Locale($alternativeLocale);
                return $object->$getter($localeObject, $alternativeLocaleObject) ?? '';
            }
        }catch(Exception){
            return '';
        }
    }


}
