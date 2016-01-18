<?php

namespace Charcoal\Translation;

/**
 * Defines an object that is influenced by or influences languages.
 *
 * This interface is useful for handling collections of languages
 * and resolving translations based on context.
 *
 * For a unilingual solution, {@see LanguageAwareInterface}.
 *
 * A multilingual interface is composed of three facets:
 *
 * 1. A _collection of languages_; to be interpreted as those available or supported.
 * 2. A _current (active) language_; one of the available languages (#1).
 * 3. A _fallback (default) language_; one of the available languages (#1).
 *    The fallback is to be relied upon when the current language (#2) cannot be resolved.
 *
 * Unless a methods requiring or returning an array, no type-hinting is imposed so
 * as to allow implementors to handle what the object supports. In Charcoal's case,
 * this could either be setting/getting strings of language codes or instances
 * of a language.
 *
 * Suggestions for accessible/mutable values:
 *
 * • _Language codes_ (identifiers) should be either a `string` (e.g., (ISO 639-1)
 *   or an `integer` (e.g., UN M.49)
 * • _Language objects_ should implement a core interface, such as
 *   {@see \Charcoal\App\Language\LanguageInterface}.
 */
interface MultilingualAwareInterface
{
    /**
     * Retrieve the object's list of available languages.
     *
     * @param  (LanguageInterface|string)[] $langs
     *     If an array of one or more lanagues is provided, the method returns
     *     a subset of the object's available languages (if any).
     * @return string[] An array of available languages
     */
    public function languages(array $langs = []);

    /**
     * Assign a list of languages to the object.
     *
     * When updating the list of available languages, the default and current language
     * is checked against the new list. If the either doesn't exist in the new list,
     * the first of the new set is used as the default language and the current language
     * is reset to NULL (which falls onto the default language).
     *
     * @param  (LanguageInterface|string)[] $langs
     *     An array of zero or more language objects or language identifiers to set
     *     on the object.
     *
     *     If an empty array is provided, the method should consider this a request
     *     to empty the languages store.
     * @return MultilingualAwareInterface Chainable
     */
    public function setLanguages(array $langs = []);

    /**
     * Add an available language to the object.
     *
     * @param  LanguageInterface|array|string $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     */
    public function addLanguage($lang);

    /**
     * Remove an available language from the object.
     *
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     */
    public function removeLanguage($lang);

    /**
     * Retrieve an available language from the object.
     *
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return LanguageInterface|string|null A language object or identifier.
     */
    public function language($lang);

    /**
     * Determine if the object has an available language.
     *
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return boolean Whether the language is available
     */
    public function hasLanguage($lang);

    /**
     * Retrieve the object's default language.
     *
     * The default language acts as a fallback when the current language
     * is not available. This is especially useful when dealing with translations.
     *
     * @return string A language identifier.
     */
    public function defaultLanguage();

    /**
     * Set the object's default language.
     *
     * Must be one of the available languages assigned to the object.
     *
     * @param  LanguageInterface|string|null $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     */
    public function setDefaultLanguage($lang = null);

    /**
     * Retrieve the object's current language.
     *
     * The current language acts as the first to be used when interacting
     * with data in a context where the language isn't explicitly specified.
     *
     * @return string A language identifier.
     */
    public function currentLanguage();

    /**
     * Set the object's current language.
     *
     * Must be one of the available languages assigned to the object.
     *
     * @param  LanguageInterface|string|null $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     */
    public function setCurrentLanguage($lang = null);
}
