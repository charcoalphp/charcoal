<?php

namespace Charcoal\Translator;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Locales Configset
 *
 * Stores the collection of available languages, their configuration structures,
 * the order of fallbacks, and the default language.
 */
class LocalesConfig extends AbstractConfig
{
    /**
     * @var array
     */
    private $languages;

    /**
     * @var string
     */
    private $defaultLanguage;

    /**
     * @var array
     */
    private $fallbackLanguages;

    /**
     * @var boolean
     */
    private $autoDetect;

    /**
     * @return array
     */
    public function defaults()
    {
        return [
            'languages' => [
                'en' => [
                    'locale' => 'en-US',
                ],
            ],
            'default_language'   => 'en',
            'fallback_languages' => [
                'en',
            ],
            'auto_detect' => false,
        ];
    }

    /**
     * @param  array $languages The languages configuration.
     * @return LocalesConfig Chainable
     */
    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
        return $this;
    }

    /**
     * @return array
     */
    public function languages()
    {
        return $this->languages;
    }

    /**
     * @param  boolean $lang The default language (ident).
     * @throws InvalidArgumentException If the default language is not a string.
     * @return LocalesConfig Chainable
     */
    public function setDefaultLanguage($lang)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Default language must be a string'
            );
        }
        $this->defaultLanguage = $lang;
        return $this;
    }

    /**
     * @return string
     */
    public function defaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * @param  array $languages The fallback languages, used when a translation is not set in a language.
     * @return LocalesConfig Chainable
     */
    public function setFallbackLanguages(array $languages)
    {
        $this->fallbackLanguages = $languages;
        return $this;
    }

    /**
     * @return array
     */
    public function fallbackLanguages()
    {
        return $this->fallbackLanguages;
    }

    /**
     * @param  boolean $autoDetect The auto-detect flag.
     * @return LocalesConfig Chainable
     */
    public function setAutoDetect($autoDetect)
    {
        $this->autoDetect = !!$autoDetect;
        return $this;
    }

    /**
     * @return boolean
     */
    public function autoDetect()
    {
        return $this->autoDetect;
    }
}
