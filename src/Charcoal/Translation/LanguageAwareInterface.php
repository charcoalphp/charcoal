<?php

namespace Charcoal\Translation;

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
     * Assign a language to the object.
     *
     * @param  string|LanguageInterface $lang A language object or identifier.
     * @return self
     */
    public function setLanguage($lang);
}
