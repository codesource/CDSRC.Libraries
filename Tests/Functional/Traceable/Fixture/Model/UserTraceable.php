<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model;

use CDSRC\Libraries\Traceable\Domain\Model\UserTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A dummy entity to test UserTraceable trait
 *
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class UserTraceable
{
    use UserTraceableTrait;

    /**
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $type;

    public function __construct($type = '')
    {
        $this->type = $type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}
