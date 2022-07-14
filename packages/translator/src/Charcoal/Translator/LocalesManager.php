<?php

namespace Charcoal\Translator;

use InvalidArgumentException;

/**
 * Locales Manager
 *
 * The manager handles the collection of available languages, their definitions,
 * the default language, and tracks the current language.
 */
class LocalesManager
{
    /**
     * Default sorting priority for locales.
     *
     * @const integer
     */
    public const DEFAULT_SORT_PRIORITY = 10;

    /**
     * Dictionary of language definitions.
     *
     * @var array
     */
    private $locales;

    /**
     * List of language codes.
     *
     * @var string[]
     */
    private $languages;

    /**
     * Language code for the default locale.
     *
     * @var string
     */
    private $defaultLanguage;

    /**
     * Language code for the current locale.
     *
     * @var string|null
     */
    private $currentLanguage;

    /**
     * Create the Locales Manager with locales and optional options.
     *
     * Required parameters:
     * - **locales** (`array`)
     *
     * Optional parameters:
     * - **default_language** (`string`)
     *   - If none is set, the first language (from _locales_) will be used.
     * - **current_language** (`string`)
     *   - If none is set, then the default language will be used.
     *
     * @param  array $data Constructor dependencies.
     * @throws InvalidArgumentException If the default language is not a valid language.
     */
    public function __construct(array $data)
    {
        $this->setLocales($data['locales']);

        $default = isset($data['default_language']) ? $data['default_language'] : null;
        $this->setDefaultLocale($default);

        $current = isset($data['current_language']) ? $data['current_language'] : null;
        $this->setCurrentLocale($current);
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
     * Set the default language.
     *
     * @param  string|null $lang The default language code.
     *    If NULL, the first language is assigned.
     * @throws InvalidArgumentException If the language is invalid.
     * @return void
     */
    private function setDefaultLocale($lang)
    {
        if ($lang === null) {
            $this->defaultLanguage = $this->languages[0];
            return;
        }

        if (!$this->hasLocale($lang)) {
            if (!is_string($lang)) {
                $lang = is_object($lang) ? get_class($lang) : gettype($lang);
            }

            throw new InvalidArgumentException(sprintf(
                'Unsupported default language; must be one of "%s", received "%s"',
                implode(', ', $this->availableLocales()),
                $lang
            ));
        }

        $this->defaultLanguage = $lang;
    }

    /**
     * Retrieve the default language.
     *
     * @return string
     */
    public function defaultLocale()
    {
        return $this->defaultLanguage;
    }

    /**
     * Set the current language.
     *
     * @param  string|null $lang The current language code.
     *    If NULL, the current language is unset.
     * @throws InvalidArgumentException If the language is invalid.
     * @return void
     */
    public function setCurrentLocale($lang)
    {
        if ($lang === null) {
            $this->currentLanguage = null;
            return;
        }

        if (!$this->hasLocale($lang)) {
            if (!is_string($lang)) {
                $lang = is_object($lang) ? get_class($lang) : gettype($lang);
            }

            throw new InvalidArgumentException(sprintf(
                'Unsupported language; must be one of "%s", received "%s"',
                implode(', ', $this->availableLocales()),
                $lang
            ));
        }

        $this->currentLanguage = $lang;
    }

    /**
     * Retrieve the current language.
     *
     * @return string
     */
    public function currentLocale()
    {
        if ($this->currentLanguage === null) {
            return $this->defaultLanguage;
        }
        return $this->currentLanguage;
    }

    /**
     * Determine if a locale is available.
     *
     * @param  string $lang The language code to check.
     * @return boolean
     */
    public function hasLocale($lang)
    {
        return isset($this->locales[$lang]);
    }

    /**
     * Set the available languages.
     *
     * Ensure that explicitely inactive locales are excluded and that the required
     * values are set on the locales configuration structure.
     *
     * This method is only called from the constructor.
     *
     * @param  array $locales The locales configuration structure.
     * @throws InvalidArgumentException If there are no active locales.
     * @return void
     */
    private function setLocales(array $locales)
    {
        $locales = $this->filterLocales($locales);
        uasort($locales, [ $this, 'sortLocalesByPriority' ]);

        $this->locales   = [];
        $this->languages = [];
        foreach ($locales as $langCode => $locale) {
            $this->locales[$langCode] = $locale;
            $this->languages[] = $langCode;
        }

        if (empty($this->locales)) {
            throw new InvalidArgumentException(
                'Locales can not be empty.'
            );
        }
    }

    /**
     * Filter the available languages.
     *
     * Routine:
     * 1. Removes disabled languages
     * 2. Assigns a priority, if absent
     * 3. Assigns a locale, if absent
     *
     * @param  array $locales The locales configuration structure.
     * @return array The parsed language structures.
     */
    private function filterLocales(array $locales)
    {
        $z = self::DEFAULT_SORT_PRIORITY;

        $filteredLocales = [];
        foreach ($locales as $langCode => $locale) {
            if (isset($locale['active']) && !$locale['active']) {
                continue;
            }

            if (!isset($locale['priority'])) {
                $locale['priority'] = $z++;
            }

            if (!isset($locale['locale'])) {
                $locale['locale'] = $langCode;
            }

            $filteredLocales[$langCode] = $locale;
        }

        return $filteredLocales;
    }

    /**
     * To be called with {@see uasort()}.
     *
     * @param  array $a Sortable action A.
     * @param  array $b Sortable action B.
     * @return integer
     */
    private function sortLocalesByPriority(array $a, array $b)
    {
        $a = isset($a['priority']) ? $a['priority'] : 0;
        $b = isset($b['priority']) ? $b['priority'] : 0;

        if ($a === $b) {
            return 0;
        }
        return ($a < $b) ? (-1) : 1;
    }
}
