<?php

namespace Charcoal\Translation;

/**
* Translation String Interface
*/
interface TranslationStringInterface
{
    /**
    * Set the current translation value(s)
    *
    * @param  TranslationStringInterface|array|string $val
    *
    * @return TranslationStringInterface Chainable
    */
    public function set_val($val);

    /**
    * Add a translation value to a specified, and available, language
    *
    * @param  string $lang An available language identifier
    * @param  string $val  The translation to be added
    *
    * @return TranslationStringInterface Chainable
    */
    public function add_val($lang, $val);

    /**
    * Get a translation value
    *
    * @param  string|null $lang Optional supported language to retrieve a translation in.
    * @return string
    */
    public function val($lang = null);

    /**
    * Set the current object's language
    *
    * @param string $lang
    * @return TranslationStringInterface Chainable
    */
    public function set_lang($lang);

    /**
    * Get the current object's language
    *
    * @return string If none was set, returns the object's default language.
    */
    public function lang();
}
