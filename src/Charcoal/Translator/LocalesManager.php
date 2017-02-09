<?php

namespace Charcoal\Translator;

use InvalidArgumentException;

/**
 *
 */
class LocalesManager
{
    /**
     * @var array
     */
    private $locales;

    /**
     * @var string[]
     */
    private $languages;

    /**
     * @var string
     */
    private $defaultLanguage;

    /**
     * @var string|null
     */
    private $currentLocale;

    /**
     * Create the Locales Manager with locales and optional options.
     *
     * Required parameters:
     * - **locales** (`array`)
     *
     * Optional parameters:
     * - **default_language** (`string`)
     *   - If none is set, the first language (from _locales_) will be used
     * - **fallback_languages** (`string[]`)
     *   - If none is set, then the default language will be used.
     *
     * @param array $data Constructor dependencies.
     * @throws InvalidArgumentException If the default language is not a valid language.
     */
    public function __construct(array $data)
    {
        $this->setLocales($data['locales']);
        $this->languages = array_keys($this->locales);

        if (isset($data['default_language'])) {
            if (!$this->hasLocale($data['default_language'])) {
                $lang = $data['default_language'];
                if (!is_string($lang)) {
                    $lang = is_object($lang) ? get_class($lang) : gettype($lang);
                }

                throw new InvalidArgumentException(sprintf(
                    'Unsupported default language; must be one of "%s", received %s',
                    implode(', ', $this->languages),
                    $lang
                ));
            }
            $this->defaultLanguage = $data['default_language'];
        } else {
            $this->defaultLanguage = $this->languages[0];
        }
    }

    /**
     * Retrieve the available locales information.
     *
     * @return array
     */
    public function locales()
    {
        return $this->locales;
    }

    /**
     * Retrieve the available locales (language codes).
     *
     * @return string[]
     */
    public function availableLocales()
    {
        return $this->languages;
    }

    /**
     * @param string|null $lang The current language (ident).
     * @throws InvalidArgumentException If the language is invalid.
     * @return void
     */
    public function setCurrentLocale($lang)
    {
        if ($lang === null) {
            $this->currentLocale = null;
            return;
        }
        if (!$this->hasLocale($lang)) {
            if (!is_string($lang)) {
                $lang = is_object($lang) ? get_class($lang) : gettype($lang);
            }

            throw new InvalidArgumentException(sprintf(
                'Unsupported language; must be one of "%s", received %s',
                implode(', ', $this->availableLocales()),
                $lang
            ));
        }
        $this->currentLocale = $lang;
    }

    /**
     * Retrieve the current language
     *
     * @return string
     */
    public function currentLocale()
    {
        if ($this->currentLocale === null) {
            return $this->defaultLanguage;
        }
        return $this->currentLocale;
    }

    /**
     * @param string $lang The language (code) to check.
     * @return boolean
     */
    public function hasLocale($lang)
    {
        return in_array($lang, $this->availableLocales());
    }

    /**
     * Ensure that explicitely inactive locales are skipped.
     * Also ensure that the required values are set on the locales configuration structure.
     * This method is only called from the constructor.
     *
     * @param array $locales The locales configuration structure.
     * @throws InvalidArgumentException If there are no active locales.
     * @return void
     */
    private function setLocales(array $locales)
    {
        $this->locales = [];
        foreach ($locales as $language => $locale) {
            if (isset($locale['active']) && !$locale['active']) {
                continue;
            }
            if (!isset($locale['locale'])) {
                $locale['locale'] = $language;
            }
            $this->locales[$language] = $locale;
        }
        if (empty($this->locales)) {
            throw new InvalidArgumentException(
                'Locales can not be empty.'
            );
        }
    }
}
