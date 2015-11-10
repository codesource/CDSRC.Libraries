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

use TYPO3\Flow\Annotations as Flow;

/**
 * Translation interface
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
interface TranslationInterface
{

    /**
     * Set parent
     *
     * @param \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface $parent
     * @param boolean $bidirectional
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface
     */
    public function setI18nParent(TranslatableInterface $parent, $bidirectional = true);

    /**
     * Get parent
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslatableInterface
     */
    public function getI18nParent();

    /**
     * Set current translation locale
     *
     * @param \TYPO3\Flow\I18n\Locale|string $locale
     *
     * @return \CDSRC\Libraries\Translatable\Domain\Model\TranslationInterface
     */
    public function setI18nLocale($locale);

    /**
     * Get current translation locale
     *
     * @return \TYPO3\Flow\I18n\Locale
     */
    public function getI18nLocale();
}
