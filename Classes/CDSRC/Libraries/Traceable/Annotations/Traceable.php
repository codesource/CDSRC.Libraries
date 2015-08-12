<?php

namespace CDSRC\Libraries\Traceable\Annotations;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use CDSRC\Libraries\Utility\AnnotationValueParser as Parser;

/**
 * Marks a class as soft deletable
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Traceable {

    /**
     * Change event (create, update, change)
     * 
     * @var string
     */
    public $on;

    /**
     * Value that should take the property on event
     * 
     * @var mixed
     */
    public $value = NULL;

    /**
     * Entity property that will allow to hard delete
     * 
     * @var string
     */
    public $field = '';

    /**
     * Conditional value of "field" to trigger event
     * 
     * @var array
     */
    public $fieldValues = array();

    /**
     * @param array $values
     * @throws \InvalidArgumentException
     */
    public function __construct(array $values) {
        if (!isset($values['on']) || !in_array($values['on'], array('create', 'update', 'change'))) {
            throw new \InvalidArgumentException('A Tracking annotation must have a target equals to create, update or change.', 1439243313);
        }
        $this->on = $values['on'];

        if (isset($values['value'])) {
            $this->value = Parser::parseValue($values['value']);
        }
        if ($this->on === 'change') {
            if (!isset($values['field']) || !is_string($values['field']) || strlen($values['field']) === 0) {
                throw new \InvalidArgumentException('A Tracking annotation must specify a field for target "change".', 1439243315);
            }
            $this->field = $values['field'];
            if (isset($values['fieldValues'])) {
                $fieldValues = Parser::parseValue($values['fieldValues']);
                $this->fieldValues = $fieldValues['type'] === Parser::VALUE_TYPE_ARRAY ? $fieldValues['content'] : array($fieldValues);
            }
        }
    }

    /**
     * Get value for entity's property
     * 
     * @param string $type
     * @param object $entity
     */
    public function getValue($type, $entity) {
        return Parser::getValueForEntity($this->value, $entity, $type);
    }
    
    /**
     * Get field values for entity's property
     * 
     * @param string $type
     * @param object $entity
     * 
     * @return array
     */
    public function getFieldValues($type, $entity){
        $values = array();
        foreach($this->fieldValues as $value){
            $values[] = Parser::getValueForEntity($value, $entity, $type);
        }
        return $values;
    }
    
    /**
     * Parse value with parser
     * 
     * @param string $value
     * @return array
     */
    protected function parseValue($value){
        return Parser::parseValue($value);
    }
}
