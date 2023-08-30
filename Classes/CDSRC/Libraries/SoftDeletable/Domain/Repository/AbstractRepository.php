<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\SoftDeletable\Domain\Repository;

use CDSRC\Libraries\SoftDeletable\Annotations\SoftDeletable;
use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Flow\Persistence\Repository;
use Neos\Flow\Reflection\ReflectionService;

/**
 * Abstract repository for SoftDeletable entities
 *
 * @Flow\Scope("singleton")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractRepository extends Repository
{

    /**
     *
     * @var boolean
     */
    protected bool $enableDeleted = false;

    /**
     * @var SoftDeletable|boolean
     */
    protected SoftDeletable|bool|null $deleteAnnotation = null;

    /**
     * @Flow\Inject
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected ReflectionService $reflectionService;

    /**
     *
     * @return $this
     */
    public function allowDeleted()
    {
        $this->enableDeleted = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): QueryResultInterface
    {
        if ($this->enableDeleted) {
            $this->entityManager->getFilters()->disable('cdsrc.libraries.softdeletable.filter');
        }
        $result = parent::findAll();
        $this->enableDeleted = false;
        $this->entityManager->getFilters()->enable('cdsrc.libraries.softdeletable.filter');

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(): int
    {
        if ($this->enableDeleted) {
            $this->entityManager->getFilters()->disable('cdsrc.libraries.softdeletable.filter');
        }
        $result = parent::countAll();
        $this->enableDeleted = false;
        $this->entityManager->getFilters()->enable('cdsrc.libraries.softdeletable.filter');

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $arguments)
    {
        if ($this->enableDeleted) {
            $this->entityManager->getFilters()->disable('cdsrc.libraries.softdeletable.filter');
        }
        $result = parent::__call($method, $arguments);
        $this->enableDeleted = false;
        $this->entityManager->getFilters()->enable('cdsrc.libraries.softdeletable.filter');

        return $result;
    }

}
