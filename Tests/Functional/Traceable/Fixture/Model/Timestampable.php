<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Functional\Traceable\Fixture\Model;

use CDSRC\Libraries\Traceable\Annotations as CDSRC;
use CDSRC\Libraries\Traceable\Domain\Model\TimestampableTrait as TimestampableTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A dummy entity to test Timestampable trait
 *
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Timestampable {
    use TimestampableTrait;

    /**
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected string $type;

    /**
     *
     * @var DateTime
     * @CDSRC\Traceable(on="change", value="now", field="type")
     * @ORM\Column(nullable=true)
     */
    protected DateTime $changedAt;

    /**
     *
     * @var DateTime
     * @CDSRC\Traceable(on="change", value="now", field="type", fieldValues="array('value1', 'value2')")
     * @ORM\Column(nullable=true)
     */
    protected DateTime $changedAtIf;

    public function __construct($type = '') {
        $this->type = $type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getChangedAt(): DateTime
    {
        return $this->changedAt;
    }

    public function getChangedAtIf(): DateTime
    {
        return $this->changedAtIf;
    }
}
