<?php

namespace CDSRC\Libraries\Traceable\Annotations;

/*******************************************************************************
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ******************************************************************************/

use CDSRC\Libraries\Utility\AnnotationValueParser as Parser;

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
    public $on;

    /**
     * Value that should take the property on event
     *
     * @var mixed
     */
    public $value = null;

    /**
     * Targeted entity property for change event
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
     * In case of an object as value, auto create if not found
     *
     * @var boolean
     */
    public $autoCreate = true;

    /**
     * Constructor
     *
     * @param array $values
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $values)
    {
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
        $this->autoCreate = isset($values['autoCreate']) && !$values['autoCreate'] ? false : true;
    }

    /**
     * Get value for entity's property
     *
     * @param string $type
     * @param object $entity
     *
     * @return mixed
     */
    public function getValue($type, $entity)
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
     */
    public function getFieldValues($type, $entity)
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
     */
    protected function parseValue($value)
    {
        return Parser::parseValue($value);
    }
}
