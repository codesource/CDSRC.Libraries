<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Model;

use Neos\Flow\I18n\Locale;

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
     * @param TranslatableInterface $parent
     * @param boolean $bidirectional
     *
     * @return TranslationInterface
     */
    public function setI18nParent(TranslatableInterface $parent, $bidirectional = true);

    /**
     * Get parent
     *
     * @return TranslatableInterface
     */
    public function getI18nParent();

    /**
     * Set current translation locale
     *
     * @param Locale|string $locale
     *
     * @return TranslationInterface
     */
    public function setI18nLocale($locale);

    /**
     * Get current translation locale
     *
     * @return Locale
     */
    public function getI18nLocale();
}
