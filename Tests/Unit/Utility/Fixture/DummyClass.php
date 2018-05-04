<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Unit\Utility\Fixture;

/**
 * Description of DummyClass
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class DummyClass
{

    protected $var;

    public function __construct($var = '')
    {
        $this->var = $var;
    }

    public function getVar()
    {
        return $this->var;
    }

    public function test1()
    {
        return 'test1';
    }

    public function test2($a, $b)
    {
        return $a . ' ' . $b;
    }

    public static function test3()
    {
        return 'test3';
    }

    public static function test4($a, $b)
    {
        return $a . ' ' . $b;
    }

    protected function protectedFunction()
    {
        return '';
    }
}
