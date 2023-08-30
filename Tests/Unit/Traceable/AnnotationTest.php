<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Unit\Traceable;

use CDSRC\Libraries\Exceptions\InvalidValueException;
use CDSRC\Libraries\Traceable\Annotations\Traceable;
use Neos\Flow\Tests\UnitTestCase;
use ReflectionException;

/**
 * Test case for the traceable annotation
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * @method assertEquals($value, $expected)
 * @method setExpectedException($class, $message, $code)
 */
class AnnotationTest extends UnitTestCase {

    /**
     * @test
     *
     * @throws InvalidValueException
     * @throws ReflectionException
     */
    public function checkAnnotationCreation() {
        // test "on"
        foreach (array('create', 'update', 'change') as $event) {
            $values = array('on' => $event, 'field' => 'someField');
            $annotation = new Traceable($values);
            $this->assertEquals($event, $annotation->on);
        }
        $this->setExpectedException('\InvalidArgumentException', '', 1439243313);
        new Traceable(array('on' => 'invalidEvent'));

        // test onChange without field attribute
        $this->setExpectedException('\InvalidArgumentException', '', 1439243315);
        new Traceable(array('on' => 'change'));
    }


}
