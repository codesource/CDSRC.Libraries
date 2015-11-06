<?php

namespace CDSRC\Libraries\Tests\Functional\Traceable;

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

use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model\IpTraceable as Entity;
use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\IpTraceableRepository;
use CDSRC\Libraries\Traceable\Utility\GeneralUtility;
use TYPO3\Flow\Persistence\Doctrine\PersistenceManager;
use TYPO3\Flow\Tests\FunctionalTestCase;

/**
 * Test case to test IpTraceable functionality
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class IpTraceableTest extends FunctionalTestCase
{

    /**
     * @var boolean
     */
    static protected $testablePersistenceEnabled = true;

    /**
     * @var \CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\IpTraceableRepository
     */
    protected $entityRepository;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        if (!$this->persistenceManager instanceof PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }
        $this->entityRepository = new IpTraceableRepository();
    }


    /**
     * @test
     */
    public function testIpTracing()
    {
        $ip = GeneralUtility::getRemoteAddress();

        $entity = new Entity();
        $this->entityRepository->add($entity);
        $this->persistenceManager->persistAll();
        $this->assertEquals($entity->getCreatedFromIp(), $ip);

        $entity->setType('testIpTracing');
        $this->entityRepository->update($entity);
        $this->persistenceManager->persistAll();
        $this->assertEquals($entity->getUpdatedFromIp(), $ip);
    }
}
