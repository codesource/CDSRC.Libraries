<?php

namespace CDSRC\Libraries\Translatable\Domain\Model;

/*******************************************************************************
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ******************************************************************************/

use CDSRC\Libraries\Translatable\Annotations as CDSRC;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Entity
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class GenericTranslation extends AbstractTranslation implements TranslationInterface
{

    /**
     * @var \Doctrine\Common\Collections\Collection<\CDSRC\Libraries\Translatable\Domain\Model\GenericTranslationField>
     * @ORM\OneToMany(mappedBy="translation", cascade={"all"}, orphanRemoval=TRUE)
     * @Flow\Lazy
     * @CDSRC\Locked
     */
    public $fields;

    /**
     * Constructor
     *
     * @param \TYPO3\Flow\I18n\Locale|string $i18nLocale
     * @param string $parentClassName
     */
    public function __construct($i18nLocale, $parentClassName = '')
    {
        parent::__construct($i18nLocale, $parentClassName);
        $this->fields = new ArrayCollection();
    }

    /**
     * Property getter
     *
     * @param string $property
     *
     * @return mixed|null
     */
    protected function get($property)
    {
        $_property = $this->sanitizeProperty($property);
        foreach ($this->fields as $field) {
            if ($field->getProperty() === $_property) {
                return $field->getValue();
            }
        }

        return null;
    }

    /**
     * Property setter
     *
     * @param string $property
     * @param mixed $value
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\GenericTranslation
     */
    protected function set($property, $value)
    {
        $_property = $this->sanitizeProperty($property);
        $field = null;
        foreach ($this->fields as $f) {
            if ($f->getProperty() === $property) {
                $field = $f;
                break;
            }
        }
        if ($field === null) {
            $field = new GenericTranslationField($this, $_property);
            $this->fields->add($field);
        }
        $field->setValue($value);

        return $this;
    }

    /**
     * Get parent class name
     *
     * @return string
     */
    protected function getParentClassName()
    {
        return $this->parentClassName;
    }
}
