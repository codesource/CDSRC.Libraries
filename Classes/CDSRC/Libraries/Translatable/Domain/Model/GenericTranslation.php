<?php

namespace CDSRC\Libraries\Translatable\Domain\Model;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use CDSRC\Libraries\Translatable\Annotations as CDSRC;

/**
 * @Flow\Entity
 * @ORM\Table(indexes={
 *  @ORM\Index(name="CDSRC_Libraries_Translatable_Reference", columns={"referencetoobject", "classnameofobject"}),
 *  @ORM\Index(name="CDSRC_Libraries_Translatable_Locale", columns={"currentlocale"})
 * })
 */
class GenericTranslation extends AbstractTranslation {

	/**
	 * @var \Doctrine\Common\Collections\Collection<\CDSRC\Libraries\Translatable\Domain\Model\GenericTranslationField>
	 * @ORM\OneToMany(mappedBy="genericTranslation", cascade={"all"}, orphanRemoval=TRUE)
     * @Flow\Lazy
     * @CDSRC\Locked
	 */
    public $fields;
    
    /**
     * Constructor
     * 
     * @param string $classname
     * @param string $locale
     */
    public function __construct($classname, $locale) {
        parent::__construct($classname, $locale);
        $this->fields = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Clone object and set the new reference
     * 
     * @param mixed $reference
     * @return CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation
     */
    public function cloneObject($reference){
        $clone = new GenericTranslation($this->getClassnameOfObject(), $this->getCurrentLocale());
        $clone->setReference($reference);
        foreach($this->fields as $field){
            $clone->set($field->getProperty(), $field->getValue());
        }
        return $clone;
    }
    
    /**
     * Check if current object can handle property
     * Property should always exists, if not it will be created at setter
     * 
     * @param string $property
     * @return boolean
     */
    protected function propertyExists($property){
        return TRUE;
    }
    
    /**
     * Property getter
     * 
     * @param string $property
     */
    protected function get($property){
        $_property = $this->sanitizeProperty($property);
        foreach($this->fields as $field){
            if($field->getProperty() === $_property){
                return $field->getValue();
            }
        }
        return NULL;
    }
    
    /**
     * Property setter
     * 
     * @param string $property
     * @param mixed $value
     * @return \CDSRC\Libraries\Translatable\Domain\Model\GenericTranslation
     */
    protected function set($property, $value){
        $_property = $this->sanitizeProperty($property);
        $field = NULL;
        foreach($this->fields as $f){
            if($f->getProperty() === $property){
                $field = $f;
                break;
            }
        }
        if($field === NULL){
            $field = new GenericTranslationField($this, $_property);
            $this->fields->add($field);
        }
        $field->setValue($value);
        return $this;
    }
}
