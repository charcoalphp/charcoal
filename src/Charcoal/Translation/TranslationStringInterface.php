<?php

namespace Charcoal\Translation;

/**
 * Defines the translatable string object
 */
interface TranslationStringInterface
{
    /**
     * Set one or more translation values
     *
     * @param  TranslationStringInterface|array|string $val
     * @return TranslationStringInterface Chainable
     */
    public function set_val($val);

    /**
     * Add a translation value to a specified, and available, language
     *
     * @param  string $lang An available language identifier
     * @param  string $val  The translation to be added
     * @return TranslationStringInterface Chainable
     */
    public function add_val($lang, $val);

    /**
     * Remove a translation value specified by an available language
     *
     * @param  string $lang
     * @return TranslationStringInterface Chainable
     */
    public function remove_val($lang);

    /**
     * Get a translation value
     *
     * @param  string|null $lang Optional supported language to retrieve a translation in.
     * @return string
     */
    public function val($lang = null);

    /**
     * Determine if the object has a specified translation
     *
     * @param  string  $lang
     * @return boolean
     * @throws InvalidArgumentException if language code isn't a string
     */
    public function has_val($lang);
}
