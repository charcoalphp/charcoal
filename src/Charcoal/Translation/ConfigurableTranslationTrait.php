<?php

namespace Charcoal\Translation;

// Intra-module (`charcoal-config`) dependency
use \Charcoal\Config\ConfigurableInterface;

// Local namespace dependency
use \Charcoal\Translation\MultilingualAwareTrait;

/**
 * An implementation of the `LanguageAwareInterface` and `ConfigurableInterface`.
 *
 * For objects needed to interact with `TranslationConfig`.
 *
 * Behavioral differences:
 *
 * • Most methods access an instance of `TranslationConfig` through
 *   the `ConfigurableInterface::config()` method.
 * • An exception to the above is the current language which refers to
 *   the object using this trait. The current language of the object can
 *   be defined exclusively from its associated `TranslationConfig`.
 *   If the object does not have an elected current language, it falls onto
 *   the `TranslationConfig`'s current language, followed by the default language.
 */
trait ConfigurableTranslationTrait
{
    use MultilingualAwareTrait;

    /**
     * Retrieve a Charcoal application's instance or a new instance of self.
     *
     * @see    Charcoal\App\Language\LanguageManager For application-wide source of instance returned.
     * @see    ConfigurableInterface::create_config() For abstract definition of this method.
     * @uses   TranslationConfig::instance()
     * @param  array|string|null $data Optional data to pass to the new TranslationConfig instance.
     * @return TranslationConfig
     */
    protected function create_config($data = null)
    {
        return TranslationConfig::instance($data);
    }

    /**
     * Retrieve the config's list of available languages.
     *
     * @uses   ConfigurableInterface::config()
     * @param  (LanguageInterface|string)[] $langs
     *     If an array of one or more lanagues is provided, the method returns
     *     a subset of the config's available languages (if any).
     * @return string[] An array of available languages.
     */
    public function languages(array $langs = [])
    {
        return $this->config()->languages($langs);
    }

    /**
     * Assign a list of languages to the config.
     *
     * When updating the list of available languages, the default and current language
     * is checked against the new list. If the either doesn't exist in the new list,
     * the first of the new set is used as the default language and the current language
     * is reset to NULL (which falls onto the default language).
     *
     * @uses   ConfigurableInterface::config()
     * @param  (LanguageInterface|string)[] $langs
     *     An array of zero or more language objects or language identifiers to set
     *     on the config.
     *
     *     If an empty array is provided, the method should consider this a request
     *     to empty the languages store.
     * @return MultilingualAwareInterface Chainable
     */
    public function set_languages(array $langs = [])
    {
        $this->config()->set_languages($langs);

        return $this;
    }

    /**
     * Add an available language to the config.
     *
     * @uses   ConfigurableInterface::config()
     * @param  LanguageInterface|array|string $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     */
    public function add_language($lang)
    {
        $this->config()->add_language($lang);

        return $this;
    }

    /**
     * Remove an available language from the config.
     *
     * @uses   ConfigurableInterface::config()
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     */
    public function remove_language($lang)
    {
        $this->config()->remove_language($lang);

        return $this;
    }

    /**
     * Retrieve an available language from the config.
     *
     * @uses   ConfigurableInterface::config()
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return LanguageInterface|string|null A language object or identifier.
     */
    public function language($lang)
    {
        return $this->config()->language($lang);
    }

    /**
     * Determine if the config has a specified language.
     *
     * @uses   ConfigurableInterface::config()
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return boolean Whether the language is available.
     */
    public function has_language($lang)
    {
        return $this->config()->has_language($lang);
    }

    /**
     * Retrieve the config's default language.
     *
     * The default language acts as a fallback when the current language
     * is not available. This is especially useful when dealing with translations.
     *
     * @uses   ConfigurableInterface::config()
     * @return string A language identifier.
     */
    public function default_language()
    {
        return $this->config()->default_language();
    }

    /**
     * Set the config's default language.
     *
     * Must be one of the available languages assigned to the config.
     *
     * @uses   ConfigurableInterface::config()
     * @param  LanguageInterface|string|null $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     */
    public function set_default_language($lang = null)
    {
        $this->config()->set_default_language($lang);

        return $this;
    }

    /**
     * Retrieve the config's current language.
     *
     * The current language acts as the first to be used when interacting
     * with data in a context where the language isn't explicitly specified.
     *
     * @uses   ConfigurableInterface::config()
     * @return string A language identifier.
     */
    public function current_language()
    {
        return $this->config()->current_language();
    }

    /**
     * Set the config's current language.
     *
     * Must be one of the available languages assigned to the config.
     *
     * Defaults to resetting the config's current language to NULL,
     * (which falls onto the default language).
     *
     * @uses   ConfigurableInterface::config()
     * @param  LanguageInterface|string|null $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     */
    public function set_current_language($lang = null)
    {
        $this->config()->set_current_language($lang);

        return $this;
    }
}
