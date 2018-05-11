<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Generic\QueryResult;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 * @method QueryResult findOneByColor($color)
 */
class CategoryRepository extends Repository
{

    // add customized methods here

}
