<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Translatable\Fixture\Model;

use CDSRC\Libraries\Translatable\Domain\Model\AbstractTranslatable;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A dummy entity
 *
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Entity extends AbstractTranslatable{

    /**
     *
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    protected ?string $type;

    /**
     * Entity constructor.
     *
     * @param string $type
     */
    public function __construct(string $type = '') {
        parent::__construct();
        $this->type = $type;
    }

	public function someMethod() {

	}
}
