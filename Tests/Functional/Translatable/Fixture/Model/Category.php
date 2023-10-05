<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use CDSRC\Libraries\Traceable\Domain\Model\TimestampableTrait;
use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslatable;
use Neos\Flow\I18n\Locale;

/**
 * @Flow\Entity
 *
 * @method Category setTitle($title, Locale $locale = null, $force = false)
 * @method string getTitle(Locale $locale = null)
 * @method CategoryTranslation getTranslationByLocale(Locale $locale, $forceCreation = false)
 */
class Category extends AbstractTranslatable
{
    use TimestampableTrait;

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected string $color;

	/** @var string */
	protected string $icon;

    /**
     * @var bool
     * @ORM\Column(name="is_active")
     */
    protected bool $isActive = true;


    public function __construct()
    {
        parent::__construct();
    }

    /** @return string */
	public function getColor(): string
    {
		return $this->color;
	}

	/**
	 * @param string $color
	 * @return Category
	 */
	public function setColor(string $color): Category
    {
		$this->color = $color;
        return $this;
	}

	/** @return string */
	public function getIcon(): string
    {
		return $this->icon;
	}

	/**
	 * @param string $icon
	 * @return Category
	 */
	public function setIcon(string $icon): Category
    {
		$this->icon = $icon;
        return $this;
	}

    /** @return bool */
    public function getIsActive() : bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return Category
     */
    public function setIsActive(bool $isActive): Category
    {
        $this->isActive = $isActive;
        return $this;
    }

	/**
	 * Return unannotated translatable fields
	 *
	 * @return array
	 */
	public static function getTranslatableFields(): array
    {
		return array('title');
	}
}
