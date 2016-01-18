<?php

namespace Charcoal\Translation;

use \InvalidArgumentException;

// Intra-module (`charcoal-app`) dependency
use \Charcoal\App\Language\Language;
use \Charcoal\App\Language\LanguageInterface;

// Local namespace dependency
use \Charcoal\Translation\MultilingualAwareTrait;

/**
 * An implementation of the `MultilingualAwareInterface`.
 *
 * A basic trait for objects needed to interact with languages defined for itself.
 *
 * @see \Charcoal\Translation\ConfigurableTranslationTrait
 *     For objects that use ConfigurableTrait. Useful for sharing a single
 *     instance of TranslationString.
 *
 *     Provides a working exampel of how to delegate language-handling to
 *     a separate object.
 *
 * Contains one additional method:
 *
 * • `self::resolve_specialLanguages()`
 */
trait TranslatableTrait
{
    use MultilingualAwareTrait;

    /**
     * List of available languages.
     *
     * @var (LanguageInterface|string)[] $languages
     */
    private $languages = [];

    /**
     * Fallback language identifier.
     *
     * @var string
     */
    private $defaultLanguage;

    /**
     * Current language identifier.
     *
     * @var string
     */
    private $currentLanguage;

    /**
     * Resolve the default and current languages.
     *
     * Utility to be called after altering the self::$languages list.
     *
     * 1. Retrieve special language directly; mitigates validating value twice.
     * 2. Validate existence of special language; if missing, reset value.
     *
     * @used-by self::setLanguages()
     * @used-by self::removeLanguage()
     * @return  MultilingualAwareInterface Chainable
     */
    public function resolveSpecialLanguages()
    {
        if (count($this->languages)) {
            if (!isset($this->languages[$this->defaultLanguage])) {
                $this->setDefaultLanguage();
            }

            if (!isset($this->languages[$this->currentLanguage])) {
                $this->setCurrentLanguage();
            }
        }

        return $this;
    }

    /**
     * Retrieve the object's list of available languages.
     *
     * @param  (LanguageInterface|string)[] $langs
     *     If an array of one or more lanagues is provided, the method returns
     *     a subset of the object's available languages (if any).
     * @return string[] An array of available languages
     */
    public function languages(array $langs = [])
    {
        $available = array_keys($this->languages);

        if (count($langs)) {
            array_walk($langs, function (&$val, $key) {
                $val = self::resolveLanguage_ident($val);
            });

            // return array_intersect_key($this->languages, array_flip($langs));
            return array_intersect($available, $langs);
        }

        return $available;
    }

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
    public function setLanguages(array $langs = [])
    {
        $this->languages = [];

        if (count($langs)) {
            foreach ($langs as $ident => $lang) {
                /** Make sure arrays are acceptable */
                if (is_array($lang) && !isset($lang['ident'])) {
                    $lang['ident'] = $ident;
                }

                $this->addLanguage($lang);
            }
        }

        $this->resolveSpecialLanguages();

        return $this;
    }

    /**
     * Add an available language to the object.
     *
     * If adding a LanguageInterface object that is already available,
     * this method will replace the existing one.
     *
     * @param  LanguageInterface|array|string $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     *
     * @throws InvalidArgumentException if an array member isn't a string or instance of LanguageInterface
     */
    public function addLanguage($lang)
    {
        if (is_string($lang)) {
            $this->languages[$lang] = $lang;
        } elseif (is_array($lang) && isset($lang['ident'])) {
            $this->languages[$lang['ident']] = $lang;
        } elseif ($lang instanceof LanguageInterface) {
            $this->languages[$lang->ident()] = $lang;
        } else {
            throw new InvalidArgumentException(
                'Must be a string-cast language code, an array, or an instance of LanguageInterface.'
            );
        }

        return $this;
    }

    /**
     * Remove an available language from the object.
     *
     * @uses   self::resolve_specialLanguages()
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     *
     * @throws InvalidArgumentException if an array member isn't a string or instance of LanguageInterface
     */
    public function removeLanguage($lang)
    {
        $lang = self::resolveLanguageIdent($lang);

        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Must be a string-cast language code or an instance of LanguageInterface.'
            );
        }

        if (isset($this->languages[$lang])) {
            unset($this->languages[$lang]);
        }

        $this->resolve_specialLanguages();

        return $this;
    }

    /**
     * Retrieve an available language from the object.
     *
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return LanguageInterface|string|null A language object or identifier.
     *
     * @throws InvalidArgumentException if an array member isn't a string or instance of LanguageInterface
     */
    public function language($lang)
    {
        $lang = self::resolveLanguageIdent($lang);

        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Must be a string-cast language code or an instance of LanguageInterface.'
            );
        }

        if (isset($this->languages[$lang])) {
            return $this->languages[$lang];
        }

        return null;
    }

    /**
     * Determine if the object has a specified language.
     *
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return boolean Whether the language is available
     */
    public function hasLanguage($lang)
    {
        return (bool)$this->language($lang);
    }

    /**
     * Retrieve the object's default language.
     *
     * The default language acts as a fallback when the current language
     * is not available. This is especially useful when dealing with translations.
     *
     * @return string A language identifier.
     */
    public function defaultLanguage()
    {
        if (!isset($this->defaultLanguage)) {
            $this->setDefaultLanguage();
        }

        return $this->defaultLanguage;
    }

    /**
     * Set the object's default language.
     *
     * Must be one of the available languages assigned to the object.
     *
     * @param  LanguageInterface|string|null $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     *
     * @throws InvalidArgumentException if language isn't available
     */
    public function setDefaultLanguage($lang = null)
    {
        if (isset($lang)) {
            $lang = self::resolveLanguageIdent($lang);

            if ($this->hasLanguage($lang)) {
                $this->defaultLanguage = $lang;
            } else {
                throw new InvalidArgumentException(
                    sprintf('Invalid language: "%s"', (string)$lang)
                );
            }
        } else {
            $languages = $this->languages();
            $this->defaultLanguage = reset($languages);
        }

        return $this;
    }

    /**
     * Retrieve the object's current language.
     *
     * The current language acts as the first to be used when interacting
     * with data in a context where the language isn't explicitly specified.
     *
     * @return string A language identifier.
     */
    public function currentLanguage()
    {
        if (!isset($this->currentLanguage)) {
            return $this->defaultLanguage();
        }

        return $this->currentLanguage;
    }

    /**
     * Set the object's current language.
     *
     * Must be one of the available languages assigned to the object.
     *
     * Defaults to resetting the object's current language to NULL,
     * (which falls onto the default language).
     *
     * @param  LanguageInterface|string|null $lang A language object or identifier.
     * @return MultilingualAwareInterface Chainable
     *
     * @throws InvalidArgumentException If language isn't available.
     */
    public function setCurrentLanguage($lang = null)
    {
        if (isset($lang)) {
            $lang = self::resolveLanguageIdent($lang);

            if ($this->hasLanguage($lang)) {
                $this->currentLanguage = $lang;
            } else {
                throw new InvalidArgumentException(
                    sprintf('Invalid language: "%s"', (string)$lang)
                );
            }
        } else {
            $this->currentLanguage = null;
        }

        return $this;
    }
}
