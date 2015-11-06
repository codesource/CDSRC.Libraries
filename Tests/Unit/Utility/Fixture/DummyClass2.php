<?php
namespace CDSRC\Libraries\Tests\Unit\Utility\Fixture;

    /* *
     * This script belongs to the TYPO3 Flow package "CDSRC.Libraries".       *
     *                                                                        *
     *                                                                        */


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
