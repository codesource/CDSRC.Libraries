<?php

namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model;

use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Entity
 */
class CategoryTranslation extends AbstractTranslation
{
    /**
     * @var string
     */
    protected $title;
}
