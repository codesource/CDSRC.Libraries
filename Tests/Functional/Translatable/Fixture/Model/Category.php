<?php
namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Weekup.Core".           *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use CDSRC\Libraries\Traceable\Domain\Model\TimestampableTrait;
use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslatable;
use CDSRC\Libraries\Translatable\Annotations as CDSRC;

/**
 * @Flow\Entity
 */
class Category extends AbstractTranslatable
{
    use TimestampableTrait;

	/** @var string */
	protected $color;

	/** @var string */
	protected $icon;

    /**
     * @var boolean
     * @ORM\Column(name="is_active")
     */
    protected $isActive = true;


    public function __construct()
    {
        parent::__construct();
        $this->setTranslatableFields(array('title'));
    }

    /** @return string */
	public function getColor() {
		return $this->color;
	}

	/**
	 * @param string $color
	 * @return Category
	 */
	public function setColor($color) {
		$this->color = $color;
        return $this;
	}

	/** @return string */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * @param string $icon
	 * @return Category
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
        return $this;
	}

    /** @return boolean */
    public function getIsActive() {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     * @return Category
     */
    public function setIsActive($isActive) {
        $this->isActive = $isActive;
        return $this;
    }
}