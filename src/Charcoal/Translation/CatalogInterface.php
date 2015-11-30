<?php

namespace Charcoal\Translation;

/**
 * Defines a collector of TranslationStringInterface objects
 */
interface CatalogInterface
{
    /**
     * Add a translation resource to the catalog.
     *
     * @param  ResourceInterface|array|string $resource
     * @return CatalogInterface Chainable
     */
    public function add_resource($resource);

    /**
     * Get the array of entries and their translations
     *
     * @param  LanguageInterface|string  $lang {
     *     If a language code is provided, the method returns
     *     a subset of entries in the specified language.
     * }
     * @return array
     */
    public function entries($lang = null);

    /**
     * Set the array of entries and their translations
     *
     * @param  mixed[]  $entries {
     *     An array of zero or more entries to set the catalog.
     *
     *     If an empty array is provided, the method should consider this a request
     *     to empty the entries store.
     * }
     * @return CatalogInterface Chainable
     */
    public function set_entries(array $entries = []);

    /**
     * Get an entry in the catalog
     *
     * @param  string  $ident  An entry's key
     * @return TranslationString
     */
    public function entry($ident);

    /**
     * Determine if the catalog has a specified entry
     *
     * @param  string  $ident  An entry's key
     * @return boolean
     */
    public function has_entry($ident);

    /**
     * Add entry to the catalog
     *
     * @param  string                            $ident         A unique key for this entry
     * @param  TranslationStringInterface|array  $translations  A set of translations
     * @return CatalogInterface Chainable
     */
    public function add_entry($ident, $translations);

    /**
     * Remove an entry from the catalog
     *
     * @param  string  $ident   An entry's key
     * @return CatalogInterface Chainable
     */
    public function remove_entry($ident);

    /**
     * Add a translation to an entry in the catalog
     *
     * @param  string  $ident  An entry's key
     * @param  string  $lang   A language identifier
     * @param  string  $val    The translation to be added
     * @return CatalogInterface Chainable
     */
    public function add_entry_translation($ident, $lang, $val);

    /**
     * Remove a translation from an entry in the catalog
     *
     * @param  string  $ident  An entry's key
     * @param  string  $lang   A language identifier
     * @param  array   $translations
     * @return CatalogInterface Chainable
     */
    public function remove_entry_translation($ident, $lang);

    /**
     * Get a translation for an entry in the catalog
     *
     * @param  string  $ident  An entry's key
     * @param  string  $lang   Optional. Defaults to the current language
     * @return string
     */
    public function translate($ident, $lang = null);
}
