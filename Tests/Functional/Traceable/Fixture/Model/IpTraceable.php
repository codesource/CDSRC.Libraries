<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model;

use CDSRC\Libraries\Traceable\Domain\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A dummy entity to test IpTraceable Trait
 *
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class IpTraceable
{
    use IpTraceableTrait;

    /**
     *
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    protected ?string $type;

    public function __construct($type = '')
    {
        $this->type = $type;
    }

    /**
     * @param string $type
     *
     * @return IpTraceable
     */
    public function setType(string $type): IpTraceable
    {
        $this->type = $type;

        return $this;
    }
}
