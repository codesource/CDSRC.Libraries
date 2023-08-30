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
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    protected ?string $type;

    /**
     * @param string $type
     */
    public function __construct(string $type = '')
    {
        $this->type = $type;
    }

    /**
     * @param string $type
     *
     * @return UserTraceable
     */
    public function setType(string $type): UserTraceable
    {
        $this->type = $type;

        return $this;
    }
}
