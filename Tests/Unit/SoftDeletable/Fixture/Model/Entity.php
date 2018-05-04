<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Tests\Unit\SoftDeletable\Fixture\Model;

use CDSRC\Libraries\SoftDeletable\Domain\Model\SoftDeletableTrait as SoftDeletable;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A dummy entity
 *
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Entity {
    use SoftDeletable;
    
    /**
     *
     * @var string 
     * @ORM\Column(nullable=true)
     */
    protected $type;
    
    public function __construct($type = '') {
        $this->type = $type;
    }
}
