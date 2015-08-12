<?php

namespace CDSRC\Libraries\Utility;

/* *
 * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
 *                                                                        *
 *                                                                        */

use CDSRC\Libraries\Exceptions\InvalidValueException;

/**
 * Marks a class as soft deletable
 *
 */
class AnnotationValueParser {

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
     * 
     * @return mixed
     */
    public static function getValueForEntity(array $config, $entity, $type = NULL) {
        switch ($config['type']) {
            case self::VALUE_TYPE_FUNCTION:
                $value = call_user_func_array($config['function'], self::buildArguments($config['arguments'], $entity));
                break;
            case self::VALUE_TYPE_METHOD:
                $class = new \ReflectionClass($config['object']);
                $obj = $class->newInstanceArgs(count($config['parameters']) > 0 ? $config['parameters'] : NULL);
                $value = call_user_func_array(array($obj, $config['method']), self::buildArguments($config['arguments'], $entity));
                break;
            case self::VALUE_TYPE_METHOD_STATIC:
                $value = forward_static_call_array(array($config['object'], $config['method']), self::buildArguments($config['arguments'], $entity));
                break;
            case self::VALUE_TYPE_ARRAY:
                $value = array();
                foreach($config['content'] as $subConfig){
                    $value[] = self::getValueForEntity($subConfig, $entity, $type);
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
                return (array) $value;
            case 'string':
                return (string) $value;
            case 'boolean':
                return $value ? TRUE : FALSE;
            default:
                if (class_exists($type)) {
                    $class = new \ReflectionClass($type);
                    if (!$class->isInstantiable()) {
                        throw new InvalidValueException('Given class "' . $type . '" is not instantiable', 1439334996);
                    }
                    $constructor = $class->getConstructor();
                    if ($constructor) {
                        if($constructor->getNumberOfRequiredParameters() > 1){
                            throw new InvalidValueException('Given class "' . $matches[7] . '" requires more than 1 parameter to be instancied', 1439334997);
                        }
                        return $class->newInstanceArgs(array($value));
                    }else{
                        return $class->newInstanceArgs();
                    }
                }
                return $value;
        }
    }

    /**
     * Parse a value data
     * 
     * @param string $value
     * 
     * @return array
     */
    public static function parseValue($value) {
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
                    throw new InvalidValueException('Given function "' . $matches[1] . '" doesn\'t exists', 1439255361);
                }
                return array(
                    'type' => self::VALUE_TYPE_FUNCTION,
                    'function' => $matches[1],
                    'arguments' => self::parseArguments($matches[2])
                );
            } elseif (preg_match('/^' . self::REGEX_METHOD . '$/', $_value, $matches)) {
                if (!class_exists($matches[1])) {
                    throw new InvalidValueException('Given class "' . $matches[1] . '" doesn\'t exists', 1439255371);
                }
                $class = new \ReflectionClass($matches[1]);
                if ($class->isAbstract()) {
                    throw new InvalidValueException('Given class "' . $matches[1] . '" is abstract', 1439255382);
                }
                if (!$class->hasMethod($matches[7])) {
                    throw new InvalidValueException('Given method "' . $matches[7] . '" doesn\'t exists', 1439255381);
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
                        throw new InvalidValueException('Given class "' . $matches[1] . '" requires ' . $numberOfRequiredParameters . ' parameter' . ($numberOfRequiredParameters > 1 ? 's' : '') . ' to be instancied', 1439255395);
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
     * Deep build argument array
     * 
     * @param array $arguments
     * @param object $entity
     * 
     * @return array
     */
    protected static function buildArguments(array $arguments, &$entity) {
        $_args = array();
        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                $_args[] = self::getValueForEntity($argument, $entity);
            } elseif ($argument === '{entity}') {
                $_args[] = & $entity;
            }else{
                $_args[] = $argument;
            }
        }
        return $_args;
    }

    /**
     * Parse arguments in an array
     * 
     * @param string $value
     * @return array
     */
    protected static function parseArguments($value) {
        if (strlen(trim($value)) === 0) {
            return array();
        }
        $open = FALSE;
        $function = FALSE;
        $previous = '';
        $escape = FALSE;
        $arguments = array();
        $argument = NULL;
        for ($i = 0, $ni = strlen($value); $i < $ni; $i++) {
            $c = $value{$i};
            $argument = $argument === NULL ? '' : $argument;
            if ($open) {
                if ($escape) {
                    $escape = FALSE;
                    $argument .= $c;
                } else {
                    if ($c === $open) {
                        $open = FALSE;
                    } elseif ($c === '\\') {
                        $escape = TRUE;
                    } else {
                        if (substr($open, 0, 4) === 'fct:') {
                            $oindex = intval(substr($open, 4));
                            if ($c === '(') {
                                $open = 'fct:' . ($oindex + 1);
                                $function = TRUE;
                            } elseif ($c === ')') {
                                $open = 'fct:' . ($oindex - 1);
                            } elseif ($c === ',') {
                                if (strlen($open) === 4) {
                                    $open = FALSE;
                                    $previous = $c;
                                    $arguments[] = trim($argument);
                                    $argument = NULL;
                                    continue;
                                } elseif ($oindex === 0) {
                                    $open = FALSE;
                                    $previous = $c;
                                    $arguments[] = self::parseValue($argument);
                                    $argument = NULL;
                                    $function = FALSE;
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
                $argument = NULL;
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
            $previous = $c;
        }
        if ($argument !== NULL) {
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
     * Parse array elements as string
     * 
     * @param string $value
     * 
     * @return array
     */
    protected static function parseArray($value) {
        $matches = array();
        $array = array();
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
        return $array;
    }

}
