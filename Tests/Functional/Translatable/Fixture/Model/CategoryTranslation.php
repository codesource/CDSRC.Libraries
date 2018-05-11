<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model;

use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslatable;
use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslation;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Entity
 */
class CategoryTranslation extends AbstractTranslation
{
    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="StringLength", options={"maximum"=200})
     */
    protected $title;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return AbstractTranslatable
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this->i18nParent;
    }
}
