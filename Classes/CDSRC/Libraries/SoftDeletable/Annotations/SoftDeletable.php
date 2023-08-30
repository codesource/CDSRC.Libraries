<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\SoftDeletable\Annotations;

/**
 * Marks a class as soft deletable
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
final class SoftDeletable
{

    /**
     * Entity property that will store deleted date
     *
     * @var string
     */
    public string $deleteProperty = 'deletedAt';

    /**
     * Entity property that will allow to hard delete
     *
     * @var string
     */
    public string $hardDeleteProperty = '';

    /**
     * Entity can be deleted in future and still be available now
     *
     * @var boolean
     */
    public bool $timeAware = false;

}
