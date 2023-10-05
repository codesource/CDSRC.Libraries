<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */

namespace CDSRC\Libraries\Translatable\Domain\Model;

use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
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
    public function setI18nParent(TranslatableInterface $parent, bool $bidirectional = true): static;

    /**
     * Get parent
     *
     * @return TranslatableInterface
     */
    public function getI18nParent(): TranslatableInterface;

    /**
     * Set current translation locale
     *
     * @param Locale|string $locale
     *
     * @return TranslationInterface
     *
     * @throws InvalidLocaleIdentifierException
     */
    public function setI18nLocale(Locale|string $locale): static;

    /**
     * Get current translation locale
     *
     * @return Locale
     *
     * @throws InvalidLocaleIdentifierException
     */
    public function getI18nLocale(): Locale;
}
