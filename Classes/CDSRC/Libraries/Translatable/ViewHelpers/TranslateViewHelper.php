<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\ViewHelpers;

use CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface;
use Neos\Flow\I18n\Locale;
use Neos\Fluid\Core\ViewHelper\AbstractViewHelper;

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
     * Store an asset or render stored assets
     *
     * @param string $property
     * @param string|Locale $locale
     * @param TranslatableInterface|null $object
     *
     * @return string
     *
     */
    public function render($property, $locale, $object = null)
    {
        if(empty($object)){
            $object = $this->renderChildren();
        }
        if(!is_object($object) || ! $object instanceof TranslatableInterface){
            return '';
        }
        try {
            $localeObject = $locale instanceof Locale ? $locale : new Locale($locale);
            $getter = 'get' .ucfirst($property);
            return $object->$getter($localeObject);
        }catch(\Exception $e){
            return '';
        }
    }


}
