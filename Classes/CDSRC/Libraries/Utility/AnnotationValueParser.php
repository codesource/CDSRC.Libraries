<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Utility;

use CDSRC\Libraries\Exceptions\InvalidValueException;

/**
 * Parse value of an annotation
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class AnnotationValueParser
{

    const VALUE_TYPE_LITERAL = 0;
    const VALUE_TYPE_FUNCTION = 1;
    const VALUE_TYPE_METHOD = 2;
    const VALUE_TYPE_METHOD_STATIC = 3;
    const VALUE_TYPE_ARRAY = 4;
    const REGEX_FUNCTION = '([a-zA-Z_][a-zA-Z0-9_]*)\s*\((.*)\)';
    const REGEX_METHOD = '((\\\\[a-zA-Z_][a-zA-Z0-9_]*)+)(::|(\((.*)\))?(->))([a-zA-Z_][a-zA-Z0-9_]*)\s*\((.*)\)';
    const REGEX_ARRAY = '[aA][rR][rR][aA][yY]\((.*)\)';

    /**
     * Generate value for an entity
     *
     * @param array $config
     * @param object $entity
     * @param string $type
     * @param boolean $forceCreation
     *
     * @return mixed
     *
     * @throws InvalidValueException
     * @throws \ReflectionException
     */
    public static function getValueForEntity(array $config, $entity, $type = null, $forceCreation = true)
    {
        return self::getValueFor($config, array('entity' => $entity), $type, $forceCreation);
    }

    /**
     * Generate value with some replacements
     *
     * @param array $config
     * @param array $for
     * @param string $type
     * @param boolean $forceCreation
     *
     * @return mixed
     *
     * @throws InvalidValueException
     * @throws \ReflectionException
     */
    public static function getValueFor(array $config, array $for = array(), $type = null, $forceCreation = true)
    {
        switch ($config['type']) {
            case self::VALUE_TYPE_FUNCTION:
                $value = call_user_func_array($config['function'], self::buildArgumentsFor($config['arguments'], $for));
                break;
            case self::VALUE_TYPE_METHOD:
                $class = new \ReflectionClass($config['object']);
                $obj = $class->newInstanceArgs(is_array($config['parameters']) && count($config['parameters']) > 0 ? $config['parameters'] : array());
                $value = call_user_func_array(array($obj, $config['method']), self::buildArgumentsFor($config['arguments'], $for));
                break;
            case self::VALUE_TYPE_METHOD_STATIC:
                $value = forward_static_call_array(array($config['object'], $config['method']), self::buildArgumentsFor($config['arguments'], $for));
                break;
            case self::VALUE_TYPE_ARRAY:
                $value = array();
                foreach ($config['content'] as $subConfig) {
                    $value[] = self::getValueFor($subConfig, $for, $type);
                }
                break;
            case self::VALUE_TYPE_LITERAL:
            default:
                $value = $config['content'];
                break;
        }
        switch ($type) {
            case '\DateTime':
                return new \DateTime(is_string($value) && strlen($value) > 0 ? $value : 'now');
            case 'integer':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'array':
                return (array)$value;
            case 'string':
                return (string)$value;
            case 'boolean':
                return $value ? true : false;
            default:
                if ($forceCreation && class_exists($type) && (!is_object($value) || !is_a($value, $type))) {
                    $class = new \ReflectionClass($type);
                    if (!$class->isInstantiable()) {
                        throw new InvalidValueException('Given class "' . $type . '" is not instantiable', 1439334996);
                    }
                    $constructor = $class->getConstructor();
                    if ($constructor) {
                        if ($constructor->getNumberOfRequiredParameters() > 1) {
                            throw new InvalidValueException('Given class "' . $type . '" requires more than 1 parameter to be instanced', 1439334997);
                        }

                        return $class->newInstanceArgs(array($value));
                    } else {
                        return $class->newInstanceArgs();
                    }
                }

                return $value;
        }
    }

    /**
     * Deep build argument array
     *
     * @param array $arguments
     * @param array $for
     *
     * @return array
     *
     * @throws InvalidValueException
     * @throws \ReflectionException
     */
    protected static function buildArgumentsFor(array $arguments, array $for)
    {
        $_args = array();
        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                $_args[] = self::getValueFor($argument, $for);
            } elseif (preg_match('/^\{.*\}$/', $argument) && strlen($key = substr($argument, 1, -1)) > 0 && isset($for[$key])) {
                $_args[] = &$for[$key];
            } else {
                $_args[] = $argument;
            }
        }

        return $_args;
    }

    /**
     * Parse a value data
     *
     * @param string $value
     *
     * @return array
     *
     * @throws InvalidValueException
     * @throws \ReflectionException
     */
    public static function parseValue($value)
    {
        $_value = trim($value);
        if (strlen($_value) > 0) {
            $matches = array();
            if (preg_match('/^' . self::REGEX_ARRAY . '$/', $_value, $matches)) {
                return array(
                    'type' => self::VALUE_TYPE_ARRAY,
                    'content' => self::parseArray($matches[1])
                );
            } elseif (preg_match('/^' . self::REGEX_FUNCTION . '$/', $_value, $matches)) {
                if (!function_exists($matches[1])) {
                    throw new InvalidValueException('Given function "' . $matches[1] . '" does\'nt exists', 1439255361);
                }

                return array(
                    'type' => self::VALUE_TYPE_FUNCTION,
                    'function' => $matches[1],
                    'arguments' => self::parseArguments($matches[2])
                );
            } elseif (preg_match('/^' . self::REGEX_METHOD . '$/', $_value, $matches)) {
                if (!class_exists($matches[1])) {
                    throw new InvalidValueException('Given class "' . $matches[1] . '" does\'nt exists', 1439255371);
                }
                $class = new \ReflectionClass($matches[1]);
                if ($class->isAbstract()) {
                    throw new InvalidValueException('Given class "' . $matches[1] . '" is abstract', 1439255382);
                }
                if (!$class->hasMethod($matches[7])) {
                    throw new InvalidValueException('Given method "' . $matches[7] . '" does\'nt exists', 1439255381);
                }
                $method = $class->getMethod($matches[7]);
                if (!$method->isPublic()) {
                    throw new InvalidValueException('Given method "' . $matches[7] . '" must be public', 1439255383);
                }
                if ($matches[3] === '::' && !$method->isStatic()) {
                    throw new InvalidValueException('Given method "' . $matches[7] . '" must be static', 1439255384);
                }
                $parameters = self::parseArguments($matches[5]);
                if ($matches[3] !== '::') {
                    if ($method->isStatic()) {
                        throw new InvalidValueException('Given method "' . $matches[7] . '" can\'t be static', 1439255385);
                    }
                    $constructor = $class->getConstructor();
                    $numberOfRequiredParameters = $constructor ? $constructor->getNumberOfRequiredParameters() : 0;
                    if ($numberOfRequiredParameters > count($parameters)) {
                        throw new InvalidValueException('Given class "' . $matches[1] . '" requires ' . $numberOfRequiredParameters . ' parameter' . ($numberOfRequiredParameters > 1 ? 's' : '') . ' to be instanced', 1439255395);
                    }
                }

                return array(
                    'type' => $matches[3] === '::' ? self::VALUE_TYPE_METHOD_STATIC : self::VALUE_TYPE_METHOD,
                    'object' => $matches[1],
                    'parameters' => $parameters,
                    'method' => $matches[7],
                    'arguments' => self::parseArguments($matches[8]),
                );
            }
        }

        return array(
            'type' => self::VALUE_TYPE_LITERAL,
            'content' => $value
        );
    }

    /**
     * Parse array elements as string
     *
     * @param string $value
     *
     * @return array
     *
     * @throws InvalidValueException
     * @throws \ReflectionException
     */
    protected static function parseArray($value)
    {
        $array = array();
        if (is_string($value)) {
            $matches = array();
            preg_match_all('/('
                . '(?:(?:"[^"]*?"|\'[^\']*?\'|[0-9]+)\s*=>\s*)?' // KEY
                . '(?:'
                . '"(?:(?:[^"]|\\")*?)"' . '\s*,\s*|'   // DOUBLE QUOTE
                . '\'(?:(?:[^\']|\\\')*?)\'' . '\s*,\s*|'   // SINGLE QUOTE
                . '(?:[a-zA-Z0-9][a-zA-Z0-9\._]*)' . '\s*,\s*|' // NUMBERS
                . '(?:(?:[a-zA-Z0-9][a-zA-Z0-9\._\->:]*)\((?:(?:.*?)\)\s*,\s*)*?(?:(?:[^\)]*)\)\s*,\s*)+?)+?' // FUNCTIONS
                . ')'
                . ')+?/', rtrim(trim($value), ',') . ',', $matches);
            foreach ($matches[1] as $match) {
                $match = rtrim(trim($match), ',');
                $parts = explode('=>', $match);
                if (count($parts) > 2) {
                    throw new InvalidValueException('Cannot parse "key" => "value" from array', 1439329941);
                }
                $value = self::parseValue(preg_replace('/^[\'"]?(.*?)[\'"]?$/', '$1', trim(array_pop($parts))));
                if (isset($parts[0])) {
                    $array[preg_replace('/^[\'"]?(.*?)[\'"]?$/', '$1', trim($parts[0]))] = $value;
                } else {
                    $array[] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * Parse arguments in an array
     *
     * @param string $value
     *
     * @return array
     *
     * @throws InvalidValueException
     * @throws \ReflectionException
     */
    protected static function parseArguments($value)
    {
        if (!is_string($value) || strlen(trim($value)) === 0) {
            return array();
        }
        $open = false;
        $function = false;
        $escape = false;
        $arguments = array();
        $argument = null;
        for ($i = 0, $ni = strlen($value); $i < $ni; $i++) {
            $c = $value{$i};
            $argument = $argument === null ? '' : $argument;
            if ($open) {
                if ($escape) {
                    $escape = false;
                    $argument .= $c;
                } else {
                    if ($c === $open) {
                        $open = false;
                    } elseif ($c === '\\') {
                        $escape = true;
                    } else {
                        if (substr($open, 0, 4) === 'fct:') {
                            $oIndex = intval(substr($open, 4));
                            if ($c === '(') {
                                $open = 'fct:' . ($oIndex + 1);
                                $function = true;
                            } elseif ($c === ')') {
                                $open = 'fct:' . ($oIndex - 1);
                            } elseif ($c === ',') {
                                if (strlen($open) === 4) {
                                    $open = false;
                                    $arguments[] = trim($argument);
                                    $argument = null;
                                    continue;
                                } elseif ($oIndex === 0) {
                                    $open = false;
                                    $arguments[] = self::parseValue($argument);
                                    $argument = null;
                                    $function = false;
                                    continue;
                                }
                            } elseif (strlen(trim($c)) === 0) {
                                $argument .= $c;
                                continue;
                            }
                        }
                        $argument .= $c;
                    }
                }
            } elseif ($c === ',') {
                $arguments[] = $argument;
                $argument = null;
            } elseif ($c === '"' || $c === "'") {
                $open = $c;
            } elseif (strlen(trim($c)) > 0) {
                $open = 'fct:';
                $argument .= $c;
            } elseif ($argument === 'new') {
                $argument .= $c;
            } else {
                continue;
            }
        }
        if ($argument !== null) {
            if (substr($open, 0, 4) === 'fct:') {
                if ($function) {
                    $arguments[] = self::parseValue($argument);
                } else {
                    $arguments[] = trim($argument);
                }
            } else {
                $arguments[] = $argument;
            }
        }

        return $arguments;
    }

    /**
     * Check if type is a function or a method
     *
     * @param integer $type
     *
     * @return boolean
     */
    public static function isFunctionOrMethod($type)
    {
        return in_array($type, array(self::VALUE_TYPE_FUNCTION, self::VALUE_TYPE_METHOD, self::VALUE_TYPE_METHOD_STATIC));
    }

    /**
     * Check if type is a literal value
     *
     * @param integer $type
     *
     * @return boolean
     */
    public static function isLiteral($type)
    {
        return $type === self::VALUE_TYPE_LITERAL;
    }

    /**
     * Check if type is an array
     *
     * @param integer $type
     *
     * @return boolean
     */
    public static function isArray($type)
    {
        return $type === self::VALUE_TYPE_ARRAY;
    }

    /**
     * Check if type is a function
     *
     * @param integer $type
     *
     * @return boolean
     */
    public static function isFunction($type)
    {
        return $type === self::VALUE_TYPE_FUNCTION;
    }

    /**
     * Check if type is a method
     *
     * @param integer $type
     *
     * @return boolean
     */
    public static function isMethod($type)
    {
        return $type === self::VALUE_TYPE_METHOD;
    }

    /**
     * Check if type is a static method
     *
     * @param integer $type
     *
     * @return boolean
     */
    public static function isStaticMethod($type)
    {
        return $type === self::VALUE_TYPE_METHOD_STATIC;
    }

}
