<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */
namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;

/**
 * A repository for Generic
 * @Flow\Scope("singleton")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class SpecificRepository extends Repository
{

    /**
     * @var string
     */
    const ENTITY_CLASSNAME = 'CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model\Specific';

}
