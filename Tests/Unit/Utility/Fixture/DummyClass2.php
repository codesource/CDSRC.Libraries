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
class DummyClass2
{

    protected $var;

    public function __construct($var)
    {
        $this->var = $var;
    }

    public function getVar()
    {
        return $this->var;
    }
}
