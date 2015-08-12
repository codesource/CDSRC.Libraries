<?php

namespace CDSRC\Libraries\Tests\Unit\Traceable;

/*
 * Copyright (C) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use CDSRC\Libraries\Traceable\Annotations\Traceable;

/**
 * Testcase for the traceable annotation
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class AnnotationTest extends \TYPO3\Flow\Tests\UnitTestCase {

    /**
     * @test
     */
    public function checkAnnotationCreation() {
        // test "on"
        foreach (array('create', 'update', 'change') as $event) {
            $values = array('on' => $event, 'field' => 'somefield');
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
