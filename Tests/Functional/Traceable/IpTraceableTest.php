<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Traceable;

use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model\IpTraceable as Entity;
use CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Repository\IpTraceableRepository;
use CDSRC\Libraries\Traceable\Utility\GeneralUtility;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Test case to test IpTraceable functionality
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * @method assertEquals($value, $expected)
 * @method markTestSkipped($message)
 */
class IpTraceableTest extends FunctionalTestCase
{

    /**
     * @var boolean
     */
    static protected $testablePersistenceEnabled = true;

    /**
     * @var IpTraceableRepository
     */
    protected IpTraceableRepository $entityRepository;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        if (!$this->persistenceManager instanceof PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }
        $this->entityRepository = new IpTraceableRepository();
    }


    /**
     * @test
     *
     * @throws IllegalObjectTypeException
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
