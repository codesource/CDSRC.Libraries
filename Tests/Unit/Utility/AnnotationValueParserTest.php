<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Unit\Utility;

use CDSRC\Libraries\Exceptions\InvalidValueException;
use CDSRC\Libraries\Utility\AnnotationValueParser as Parser;
use Neos\Flow\Tests\UnitTestCase;

/**
 * Test case for the traceable annotation
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * @method assertEquals($value, $expected)
 * @method setExpectedException($class, $message, $code)
 */
class AnnotationValueParserTest extends UnitTestCase
{

    /**
     * dataProvider for translation testing
     */
    public function getValuesResults()
    {
        return array(
            // Literal
            array('2', array(
                'type' => Parser::VALUE_TYPE_LITERAL,
                'content' => 2
            )),
            array('\'', array(
                'type' => Parser::VALUE_TYPE_LITERAL,
                'content' => '\''
            )),
            array('"quoted string"', array(
                'type' => Parser::VALUE_TYPE_LITERAL,
                'content' => '"quoted string"'
            )),
            array('"quoted_function()"', array(
                'type' => Parser::VALUE_TYPE_LITERAL,
                'content' => '"quoted_function()"'
            )),

            // Functions
            array('date()', array(
                'type' => Parser::VALUE_TYPE_FUNCTION,
                'function' => 'date',
                'arguments' => array()
            )),
            array('date("Y-m-d H:i:s")', array(
                'type' => Parser::VALUE_TYPE_FUNCTION,
                'function' => 'date',
                'arguments' => array('Y-m-d H:i:s')
            )),
            array('substr(date("Y-m-d H:i:s"), 0, 10)', array(
                'type' => Parser::VALUE_TYPE_FUNCTION,
                'function' => 'substr',
                'arguments' => array(
                    array(
                        'type' => Parser::VALUE_TYPE_FUNCTION,
                        'function' => 'date',
                        'arguments' => array('Y-m-d H:i:s')
                    ),
                    0,
                    10
                )
            )),
            array('rtrim ( substr (date ("Y-m-d H:i:s") , rand(  0 ,2) , rand ( 2, 3)), \'\\\\\')', array(
                'type' => Parser::VALUE_TYPE_FUNCTION,
                'function' => 'rtrim',
                'arguments' => array(
                    array(
                        'type' => Parser::VALUE_TYPE_FUNCTION,
                        'function' => 'substr',
                        'arguments' => array(
                            array(
                                'type' => Parser::VALUE_TYPE_FUNCTION,
                                'function' => 'date',
                                'arguments' => array('Y-m-d H:i:s')
                            ),
                            array(
                                'type' => Parser::VALUE_TYPE_FUNCTION,
                                'function' => 'rand',
                                'arguments' => array(0, 2)
                            ),
                            array(
                                'type' => Parser::VALUE_TYPE_FUNCTION,
                                'function' => 'rand',
                                'arguments' => array(2, 3)
                            ),
                        )
                    ),
                    '\\'
                )
            )),
            array('___invalid_function()', array(), InvalidValueException::class, 1439255361),
            array('unclosed_function(', array(
                'type' => Parser::VALUE_TYPE_LITERAL,
                'content' => 'unclosed_function('
            )),

            // Methods
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass("value")->getVar()', array(
                'type' => Parser::VALUE_TYPE_METHOD,
                'object' => '\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass',
                'parameters' => array('value'),
                'method' => 'getVar',
                'arguments' => array()
            )),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass->test1()', array(
                'type' => Parser::VALUE_TYPE_METHOD,
                'object' => '\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass',
                'parameters' => array(),
                'method' => 'test1',
                'arguments' => array()
            )),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass()->test2(25, "abc")', array(
                'type' => Parser::VALUE_TYPE_METHOD,
                'object' => '\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass',
                'parameters' => array(),
                'method' => 'test2',
                'arguments' => array(25, 'abc')
            )),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass::test3()', array(
                'type' => Parser::VALUE_TYPE_METHOD_STATIC,
                'object' => '\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass',
                'parameters' => array(),
                'method' => 'test3',
                'arguments' => array()
            )),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass::test4("abc", 25)', array(
                'type' => Parser::VALUE_TYPE_METHOD_STATIC,
                'object' => '\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass',
                'parameters' => array(),
                'method' => 'test4',
                'arguments' => array('abc', 25)
            )),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClassNotExisting::test()', array(), InvalidValueException::class, 1439255371),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClassAbstract::abstractFunction()', array(), InvalidValueException::class, 1439255382),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass::notExistingFunction()', array(), InvalidValueException::class, 1439255381),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass::protectedFunction()', array(), InvalidValueException::class, 1439255383),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass::test1()', array(), InvalidValueException::class, 1439255384),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass->test3()', array(), InvalidValueException::class, 1439255385),
            array('\CDSRC\Libraries\Tests\Unit\Utility\Fixture\DummyClass2->getVar()', array(), InvalidValueException::class, 1439255395),


            // Array
            array('array("test", 2, 4, "key" => ",", date("Y-m-d"))', array(
                'type' => Parser::VALUE_TYPE_ARRAY,
                'content' => array(
                    array(
                        'type' => Parser::VALUE_TYPE_LITERAL,
                        'content' => 'test'
                    ),
                    array(
                        'type' => Parser::VALUE_TYPE_LITERAL,
                        'content' => 2
                    ),
                    array(
                        'type' => Parser::VALUE_TYPE_LITERAL,
                        'content' => '4'
                    ),
                    'key' => array(
                        'type' => Parser::VALUE_TYPE_LITERAL,
                        'content' => ','
                    ),
                    array(
                        'type' => Parser::VALUE_TYPE_FUNCTION,
                        'function' => 'date',
                        'arguments' => array("Y-m-d")
                    ))
            )),
        );
    }

    /**
     * Check all value parsing case
     *
     * @param string $value
     * @param array $result
     * @param string $throwException
     * @param integer $throwExceptionCode
     *
     * @test
     * @dataProvider getValuesResults
     *
     * @throws InvalidValueException
     * @throws \ReflectionException
     */
    public function checkValueParsing($value, $result, $throwException = NULL, $throwExceptionCode = NULL)
    {
        $this->setExpectedException($throwException, '', $throwExceptionCode);
        $this->assertEquals($result, Parser::parseValue($value));
    }


}
