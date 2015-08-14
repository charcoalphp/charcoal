<?php

namespace Charcoal\Translation;

/**
* Translation String Interface
*/
interface TranslationStringInterface
{

    /**
    * @param TranslationStringInterface|array|string $val
    * @return TranslationString Chainable
    */
    public function set_val($val);

    /**
    * @param string $lang
    * @param string $val
    * @throws InvalidArgumentException
    * @return TranslationString Chainable
    */
    public function add_val($lang, $val);

    /**
    * Get the the translation values.
    *
    * The returned value depends on the parameter:
    * - if a `$lang`, then a string containing the translated string for this language
    * - if `null`, then an array containing all languag strings
    *
    * @return string
    */
    public function val($lang = null);

    /**
    * Set the current object's language.
    *
    * @param string $lang
    * @return TranslationStringInterface Chainable
    */
    public function set_lang($lang);

    /**
    * Get the current language.
    * If none was set, this function returns the configration's default language.
    * @return string
    */
    public function lang();
}
