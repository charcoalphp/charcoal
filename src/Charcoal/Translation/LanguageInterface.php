<?php

namespace Charcoal\Translation;

interface LanguageInterface
{
    public function set_data(array $data);
    public function set_ident($ident);
    public function ident();
    public function set_name($name);
    public function name();
    /**
    * Get the ISO-639-1 (2-character) language code.
    * @return string
    */
    public function iso639();

    /**
    * Set the language's locale
    */
    public function set_locale($locale);
    /**
    *
    */
    public function locale();
}
