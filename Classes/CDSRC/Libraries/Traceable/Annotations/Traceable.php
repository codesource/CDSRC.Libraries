<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Traceable\Annotations;

use CDSRC\Libraries\Exceptions\InvalidValueException;
use CDSRC\Libraries\Utility\AnnotationValueParser as Parser;
use InvalidArgumentException;
use ReflectionException;

/**
 * Marks a property as traceable
 *
 * @Annotation
 * @Target("PROPERTY")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
final class Traceable
{

    /**
     * Change event (create, update, change)
     *
     * @var string
     */
    public string $on;

    /**
     * Value that should take the property on event
     *
     * @var mixed
     */
    public mixed $value = null;

    /**
     * Targeted entity property for change event
     *
     * @var string
     */
    public string $field = '';

    /**
     * Conditional value of "field" to trigger event
     *
     * @var array
     */
    public array $fieldValues = array();

    /**
     * In case of an object as value, auto create if not found
     *
     * @var bool
     */
    public bool $autoCreate = true;

    /**
     * Constructor
     *
     * @param array $values
     *
     * @throws InvalidArgumentException
     * @throws InvalidValueException
     * @throws ReflectionException
     */
    public function __construct(array $values)
    {
        if (!isset($values['on']) || !in_array($values['on'], array('create', 'update', 'change'))) {
            throw new InvalidArgumentException('A Tracking annotation must have a target equals to create, update or change.', 1439243313);
        }
        $this->on = $values['on'];

        if (isset($values['value'])) {
            $this->value = Parser::parseValue($values['value']);
        }
        if ($this->on === 'change') {
            if (!isset($values['field']) || !is_string($values['field']) || strlen($values['field']) === 0) {
                throw new InvalidArgumentException('A Tracking annotation must specify a field for target "change".', 1439243315);
            }
            $this->field = $values['field'];
            if (isset($values['fieldValues'])) {
                $fieldValues = Parser::parseValue($values['fieldValues']);
                $this->fieldValues = $fieldValues['type'] === Parser::VALUE_TYPE_ARRAY ? $fieldValues['content'] : array($fieldValues);
            }
        }
        $this->autoCreate = !(isset($values['autoCreate']) && !$values['autoCreate']);
    }

    /**
     * Get value for entity's property
     *
     * @param string $type
     * @param object $entity
     *
     * @return mixed
     *
     * @throws InvalidValueException
     * @throws ReflectionException
     */
    public function getValue(string $type, object $entity): mixed
    {
        return Parser::getValueForEntity($this->value, $entity, $type, $this->autoCreate);
    }

    /**
     * Get field values for entity's property
     *
     * @param string $type
     * @param object $entity
     *
     * @return array
     *
     * @throws InvalidValueException
     * @throws ReflectionException
     */
    public function getFieldValues(string $type, object $entity)
    {
        $values = array();
        foreach ($this->fieldValues as $value) {
            $values[] = Parser::getValueForEntity($value, $entity, $type, $this->autoCreate);
        }

        return $values;
    }

    /**
     * Parse value with parser
     *
     * @param string $value
     *
     * @return array
     *
     * @throws InvalidValueException
     * @throws ReflectionException
     */
    protected function parseValue(string $value): array
    {
        return Parser::parseValue($value);
    }
}
