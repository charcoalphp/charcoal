<?php

namespace Charcoal\Translation;

// Intra-module (`charcoal-app`) dependency
use \Charcoal\App\Language\LanguageInterface;

/**
 * Defines an object that can track a single language value.
 *
 * This interface is useful for handling a unilingual contexts.
 *
 * For a multilingual solution, {@see MultilingualAwareInterface}.
 */
interface LanguageAwareInterface
{
    /**
     * Retrieve the object's current language.
     *
     * @return string A language identifier.
     */
    public function language();

    /**
     * Set the object's current language.
     *
     * @param  LanguageInterface|string|null $lang A language object or identifier.
     * @return self
     */
    public function set_language($lang = null);
}
