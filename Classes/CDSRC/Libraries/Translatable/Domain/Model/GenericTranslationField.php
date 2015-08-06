<?php

namespace CDSRC\Libraries\Translatable\Domain\Model;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class GenericTranslationField {
    
    /**
     * @var string
     * @Flow\Transient
     */
    const RELATED_TRAIT = 'CDSRC\\Libraries\\Translatable\\Domain\\Model\\TraitTranslatable';

    /**
     * @Flow\Inject
     * @Flow\Transient
     * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;
    

	/**
	 * @var \CDSRC\Libraries\Translatable\Domain\Model\GenericTranslation
	 * @ORM\ManyToOne(inversedBy="fields")
	 */
    protected $genericTranslation;

    /**
     * Property's name
     * 
     * @var string
     */
    protected $property;

    /**
     * Generic boolean
     * 
     * @var boolean
     * @ORM\Column(nullable=true)
     */
    protected $vboolean;

    /**
     * Generic integer
     * 
     * @var integer
     * @ORM\Column(nullable=true)
     */
    protected $vinteger;

    /**
     * Generic float
     * Mapped as double
     * 
     * @var float
     * @ORM\Column(nullable=true)
     */
    protected $vfloat;

    /**
     * Generic string
     * 
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $vstring;

    /**
     * Generic string
     * 
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $vtext;

    /**
     * Generic datetime
     * 
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $vdatetime;
    
    /**
     * Generic array
     * 
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $varray;
    
    /**
     * Generic array
     * 
     * @var array
     * @Flow\Transient
     */
    protected $varray_unserizalized;
    

    /**
     * Generic reference to a translatable object
     * 
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $objectReference;

    /**
     * Generic class name of the translatable object
     * 
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $objectClassName;

    /**
     * Available value type
     * 
     * @var array 
     * @Flow\Transient
     */
    protected $types = array('vboolean', 'vinteger', 'vfloat', 'vstring', 'vtext', 'vdatetime', 'varray', 'objectReference', 'objectClassName');

    /**
     * Contructor
     * @param string $property
     * @throws \TYPO3\Flow\Property\Exception\InvalidPropertyException
     */
    public function __construct(\CDSRC\Libraries\Translatable\Domain\Model\GenericTranslation $translation, $property) {
        if (!is_string($property) || !preg_match('/^[a-z][a-z0-9_]+$/i', $property)) {
            throw new \TYPO3\Flow\Property\Exception\InvalidPropertyException('"' . $property . '" is not a valid property\'s name', 1428263235);
        }
        $this->genericTranslation = $translation;
        $this->property = (string) $property;
    }
    
    /**
     * Return the field's property
     * @return string
     */
    public function getProperty(){
        return $this->property;
    }

    /**
     * Return value issues from specific type
     * 
     * @return mixed
     */
    public function getValue() {
        foreach ($this->types as $type) {
            if ($type !== 'objectClassName' && $this->$type !== NULL) {
                if($type === 'varray'){
                    if(!is_array($this->varray_unserizalized) && is_string($this->varray) && strlen($this->varray) > 0){
                        $this->varray_unserizalized = unserialize($this->varray);
                    }
                    return $this->varray_unserizalized;
                }elseif($type === 'objectReference'){
                    $object = $this->persistenceManager->getObjectByIdentifier($this->objectReference, $this->objectClassName);
                    if($object !== NULL){
                        return $object;
                    }
                }else{
                    return $this->$type;
                }
            }
        }
        return NULL;
    }

    /**
     * Set value
     * @param mixed $value
     * @return \CDSRC\Libraries\Translatable\Domain\Model\GenericTranslationField
     */
    public function setValue($value) {
        $OK = FALSE;
        if (is_bool($value)) {
            $this->vboolean = $value;
            $OK = TRUE;
        } elseif(is_integer($value)){
            $this->vinteger = $value;
            $OK = TRUE;
        }elseif(is_float($value)){
            $this->vfloat = $value;
            $OK = TRUE;
        }elseif(is_string($value)){
            if(strlen($value) > 255){
                $this->vtext = $value;
            }else{
                $this->vstring = $value;
            }
            $OK = TRUE;
        }elseif(is_array($value)){
            $this->varray_unserizalized = $value;
            $serialized = $this->serializeArray($value);
            if($serialized === FALSE){
                $this->varray_unserizalized = NULL;
                $OK = FALSE;
            }else{
                $this->varray = $serialized;
                $OK = TRUE;
            }
        }elseif(is_object($value)){
            if($value instanceof \DateTime){
                $this->vdatetime = $value;
                $OK = TRUE;
            }elseif(\CDSRC\Libraries\Utility\GeneralUtility::useTrait($value, self::RELATED_TRAIT)){
                $this->objectReference = $this->persistenceManager->getIdentifierByObject($value);
                if($this->objectReference !== NULL && strlen($this->objectReference) > 0){
                    $this->objectClassName = get_class($value);
                    $OK = TRUE;
                }
            }
        }
        if(!$OK){
            throw new \TYPO3\Flow\Property\Exception\InvalidDataTypeException('Given value can\'t be translated by generic translation.', 1428269963);
        }
        return $this;
    }

    /**
     * Reset all values
     * @return \CDSRC\Libraries\Translatable\Domain\Model\GenericTranslationField
     */
    protected function resetValues() {
        foreach ($this->types as $type) {
            $this->$type = NULL;
        }
        return $this;
    }

    /**
     * Serialize an array
     * 
     * @param array $array
     * 
     * @return string|FALSE if error appends
     */
    protected function serializeArray(array &$array, $depth = 0){
        if($depth < 50){
            foreach($array as &$val){
                if(is_scalar($val)){
                    continue;
                }elseif(is_array($val)){
                    $serialized = $this->serializeArray($val, $depth + 1);
                    if($serialized === FALSE){
                        return FALSE;
                    }else{
                        $val = 'ARR->|' . $serialized;
                    }
                }elseif(is_object($val)){
                    if($val instanceof \DateTime){
                        $val = 'DAT->|' . serialize($val);
                    }else{
                        $className = get_class($val);
                        if(\CDSRC\Libraries\Utility\GeneralUtility::useTrait($className, self::RELATED_TRAIT)){
                            $ref = $this->persistenceManager->getIdentifierByObject($val);
                            if($ref !== NULL && strlen($ref) > 0){
                                $val = 'OBJ->|' . $className . '|' . $ref;
                            }else{
                                return FALSE;
                            }
                        }
                    }
                }else{
                    return FALSE;
                }
            }
            return serialize($array);
        }
        return FALSE;
    }
    
    /**
     * Unserialize an array
     * 
     * @param string $string
     * 
     * @return array|FALSE if error appends
     */
    protected function unserializeArray($string){
        $array = unserialize($string);
        if(is_array($array)){
            foreach($array as &$val){
                if(is_string($val)){
                    switch(substr($val, 0, 6)){
                        case 'ARR->|':
                            $val = $this->unserializeArray(substr($val, 6));
                            if($val === FALSE){
                                return FALSE;
                            }
                            break;
                        case 'DAT->|':
                            $val = unserialize(substr($val, 6));
                            break;
                        case 'OBJ->|':
                            list($className, $reference) = explode('|', substr($val, 6));
                            if(strlen($className) > 0 && strlen($reference) > 0){
                                $val = $this->persistenceManager->getObjectByIdentifier($reference, $className);
                                if($val === FALSE){
                                    return FALSE;
                                }
                            }
                            break;
                    }
                }
            }
            return $array;
        }
        return FALSE;
    }
}
