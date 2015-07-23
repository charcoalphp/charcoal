<?php

namespace Charcoal\Translation;

interface CatalogInterface
{
    /**
    * Add a translation resource to the catalog.
    *
    * @param ResourceInterface|array|string $resource
    * @return CatalogInterface Chainable
    */
    public function add_resource($resource);

    /**
    * Add a translation to the catalog
    *
    * @param string $ident
    * @param array $translations
    * *@return CatalogInterface Chainable
    */
    public function add_translation($ident, $translations);

    /**
    * Get a string translation
    *
    * @param string $ident;
    * @param string $lang Optional. Use current / default language if omitted.
    */
    public function tr($ident, $lang = null);

    /**
    * Set the current catalog's language.
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
