<?php

namespace Charcoal\Translation;

use \ArrayAccess;
use \InvalidArgumentException;

// Intra-module (`charcoal-config`) dependencies
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\Translation\CatalogInterface;
use \Charcoal\Translation\ConfigurableTranslationTrait;
use \Charcoal\Translation\MultilingualAwareInterface;
use \Charcoal\Translation\TranslationString;
use \Charcoal\Translation\TranslationStringInterface;

/**
 * Translation Catalog Object
 *
 * A collection of translatable strings.
 *
 * Except for `self::$currentLanguage`, this configurable object delegates
 * most of its multi-language handling to TranslationConfig.
 *
 * The TranslationString class provides it's own independant `self::$currentLanguage`.
 * The class redefines the following methods from ConfigurableTranslationTrait:
 *
 * • `self::defaultLanguage()` → `ConfigurableTranslationTrait::currentLanguage()`
 * • `self::currentLanguage()` → `self::$currentLanguage`
 * • `self::setCurrentLanguage()` → `self::$currentLanguage`
 *
 * @see TranslationString for a similar exception for the current language.
 */
class Catalog implements
    CatalogInterface,
    ConfigurableInterface,
    MultilingualAwareInterface,
    ArrayAccess
{
    use ConfigurableTrait;
    use ConfigurableTranslationTrait;

    /**
     * The array of translations, as a `$ident => TranslationStringInterface` hash.
     *
     * @var TranslationStringInterface[] $entries
     */
    private $entries = [];

    /**
     * Current language identifier.
     *
     * @var string
     */
    private $currentLanguage;

    /**
     * Calling the constructor with a parameter should force setting it up as value.
     *
     * @param  mixed[]                 $entries An array of entries, each composed of translations.
     * @param  TranslationConfig|array $config
     * @return self
     */
    public function __construct(array $entries = null, $config = null)
    {
        if (isset($config)) {
            $this->set_config($config);
        }

        if (isset($entries)) {
            $this->setEntries($entries);
        }

        return $this;
    }

    /**
     * Add a translation resource to the catalog.
     *
     * @param  ResourceInterface|array|string  $resource
     * @throws InvalidArgumentException
     * @return self
     * @todo   Implement handling of a ResourceInterface instance.
     */
    public function addResource($resource)
    {
        if ($resource instanceof ResourceInterface) {
            throw new InvalidArgumentException('ResourceInterface object not (yet) supported.');
        } elseif (is_array($resource)) {
            foreach ($resource as $ident => $translations) {
                $this->addTranslations($ident, $translations);
            }
        } elseif (is_string($resource)) {
            throw new InvalidArgumentException('String resource not (yet) supported.');
        }

        return $this;
    }

    /**
     * Get the catalog's list of entries
     *
     * Defaults to retrieving all entry identifiers.
     * Optionally, the list can be filtered by a language code.
     *
     * @param  LanguageInterface|string  $lang {
     *     If a language code is provided, the method returns
     *     a subset of entries in the specified language.
     * }
     * @return array
     * @throws InvalidArgumentException if language is invalid
     */
    public function entries($lang = null)
    {
        $lang = self::resolveLanguageIdentent($lang);

        if (isset($lang)) {
            if ($this->hasLanguage($lang)) {
                $entries = [];

                foreach ($this->entries as $ident => $translations) {
                    if (isset($translations[$lang])) {
                        $entries[] = $ident;
                    }
                }

                return $entries;
            } else {
                throw new InvalidArgumentException(sprintf('Invalid language: "%s"', (string)$lang));
            }
        } else {
            return array_keys($this->entries);
        }
    }

    /**
     * Set the array of entries and their translations
     *
     * @param  mixed[]  $entries {
     *     An array of zero or more entries to set the catalog.
     *
     *     If an empty array is provided, the method should consider this a request
     *     to empty the entries store.
     * }
     * @return self
     */
    public function setEntries(array $entries = [])
    {
        $this->entries = [];

        if (count($entries)) {
            foreach ($entries as $ident => $translations) {
                $this->addEntry($ident, $translations);
            }
        }

        return $this;
    }

    /**
     * Get an entry in the catalog
     *
     * @param  string  $ident  An entry's key
     * @return TranslationString
     * @throws InvalidArgumentException if the idenfitier isn't a string
     */
    public function entry($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException('Entry identifier must be a string.');
        }

        if ($this->hasEntry($ident)) {
            return $this->entries[$ident];
        }
    }

    /**
     * Determine if the catalog has a specified entry
     *
     * @param  string  $ident  An entry's key
     * @return boolean
     * @throws InvalidArgumentException if the idenfitier isn't a string
     */
    public function hasEntry($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Entry identifier must be a string.'
            );
        }

        return isset($this->entries[$ident]);
    }

    /**
     * Add entry to the catalog
     *
     * This method will replace an existing entry.
     *
     * @param  string                            $ident         A unique key for this entry
     * @param  TranslationStringInterface|array  $translations  A set of translations
     * @return self
     * @throws InvalidArgumentException if the idenfitier isn't a string
     */
    public function addEntry($ident, $translations = null)
    {
        if (!is_string($ident)) {
            if ($ident instanceof TranslationStringInterface) {
                $translations = $ident->all();
            } elseif (is_array($ident)) {
                $translations = $ident;
            } else {
                throw new InvalidArgumentException(
                    'Entry identifier must be a string.'
                );
            }

            $lang = $this->defaultLanguage();
            if (isset($translations[$lang])) {
                $ident = $translations[$lang];
            } else {
                throw new InvalidArgumentException(
                    'Entry identifier must be a string.'
                );
            }
        }

        if ($translations instanceof TranslationStringInterface) {
            $translations = $translations->all();
        }

        if (!is_array($translations)) {
            throw new InvalidArgumentException(
                'Translations must be an array or an instance of TranslationStringInterface.'
            );
        }

        $entry = new TranslationString($translations, $this->config());

        $this->entries[$ident] = $entry;

        return $this;
    }

    /**
     * Remove an entry from the catalog
     *
     * @param  string  $ident  An entry's key
     * @return self
     * @throws InvalidArgumentException if the idenfitier isn't a string
     */
    public function removeEntry($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Entry identifier must be a string.'
            );
        }

        if ($this->hasEntry($ident)) {
            unset($this->entries[$ident]);
        }
    }

    /**
     * Add a translation to an entry in the catalog
     *
     * @param  string                    $ident  An entry's key
     * @param  LanguageInterface|string  $lang   A language object or identifier
     * @param  string                    $val    The translation to be added
     * @return self
     * @throws InvalidArgumentException if entry key, entry value, or language is invalid
     */
    public function addEntryTranslation($ident, $lang, $val)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Entry identifier must be a string.'
            );
        }

        if ($this->hasEntry($ident)) {
            $this->entries[$ident]->add_val($lang, $val);
        } else {
            if (!is_string($val)) {
                throw new InvalidArgumentException(
                    'Localized value must be a string.'
                );
            }

            $lang = self::resolveLanguageIdentent($lang);

            $this->addEntry(
                $ident,
                [
                    $lang => $val
                ]
            );
        }
    }

    /**
     * Remove a translation from an entry in the catalog
     *
     * @param  string                    $ident  An entry's key
     * @param  LanguageInterface|string  $lang   A language object or identifier
     * @return self
     * @throws InvalidArgumentException if the idenfitier isn't a string
     */
    public function removeEntryTranslation($ident, $lang)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Entry identifier must be a string.'
            );
        }

        if ($this->hasEntry($ident)) {
            $lang = self::resolveLanguageIdentent($lang);

            unset($this->entries[$ident][$lang]);
        }
    }

    /**
     * Get a translation for an entry in the catalog
     *
     * @param  string                    $ident  An entry's key
     * @param  LanguageInterface|string  $lang   Optional. Defaults to the current language
     * @return string
     * @throws InvalidArgumentException if entry key or language is invalid
     */
    public function translate($ident, $lang = null)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Entry identifier must be a string.'
            );
        }

        if (!isset($lang)) {
            $lang = $this->currentLanguage();
        }

        $lang = self::resolveLanguageIdentent($lang);

        if ($this->hasLanguage($lang)) {
            if ($this->hasEntry($ident)) {
                if (isset($this->entries[$ident][$lang])) {
                    return $this->entries[$ident][$lang];
                }
            }
        }

        return $ident;
    }

    /**
     * Determine if translation exists.
     *
     * Called when using the objects as `isset($obj['foo'])`.
     *
     * @see    ArrayAccess::offsetExists()
     * @param  string  $ident
     * @return boolean
     * @throws InvalidArgumentException if array key isn't a string
     */
    public function offsetExists($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Array key must be a string.'
            );
        } else {
            return $this->hasEntry($ident);
        }
    }

    /**
     * Alias of `self::translate()`
     *
     * @see    ArrayAccess::offsetGet()
     * @param  string  $ident
     * @return string
     * @throws InvalidArgumentException if array key isn't a string
     */
    public function offsetGet($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Array key must be a string.'
            );
        }

        return $this->translate($ident);
    }

    /**
     * Alias of `self::addTranslations()` and `self::addTranslationLanguage()`
     * depending on value assigned.
     *
     * @see    ArrayAccess::offsetSet()
     * @param  string  $ident
     * @param  mixed   $val
     * @return void
     * @throws InvalidArgumentException if array key isn't a string
     */
    public function offsetSet($ident, $value)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Array key must be a string.'
            );
        }

        if (is_array($value) || ($value instanceof TranslationStringInterface)) {
            $this->addEntry($ident, $value);
        } elseif (is_string($value)) {
            $lang = $this->currentLanguage();
            $lang = self::resolveLanguageIdentent($lang);
            $this->addEntryTranslation($ident, $lang, $value);
        } else {
            throw new InvalidArgumentException(
                'Invalid value argument.'
            );
        }

    }
    /**
     * Alias of `self::removeTranslation()`
     *
     * Called when using `unset($obj['foo']);`.
     *
     * @see    ArrayAccess::offsetUnset()
     * @param  string  $ident
     * @return void
     * @throws InvalidArgumentException if array key isn't a string
     */
    public function offsetUnset($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Array key must be a string.'
            );
        }

        $this->removeEntry($ident);
    }

    /**
     * Get the config's default language
     *
     * @uses   ConfigurableInterface::config()
     * @return string  A language identifier
     */
    public function defaultLanguage()
    {
        return $this->config()->defaultLanguage();
    }

    /**
     * Get the object's current language
     *
     * The current language acts as the first to be used when interacting
     * with data in a context where the language isn't explicitly specified.
     *
     * @see    TranslatableTrait::currentLanguage()
     * @return string  A language identifier
     */
    public function currentLanguage()
    {
        if (!isset($this->currentLanguage)) {
            return $this->config()->currentLanguage();
        }

        return $this->currentLanguage;
    }

    /**
     * Set the object's current language.
     *
     * Must be one of the available languages assigned to the object.
     *
     * Defaults to resetting the object's current language to the config's,
     * (which might fall onto the default language).
     *
     * @see    TranslatableTrait::setCurrentLanguage()
     * @param  LanguageInterface|string|null  $lang  A language object or identifier
     * @return self
     *
     * @throws InvalidArgumentException if language isn't available
     */
    public function setCurrentLanguage($lang = null)
    {
        if (isset($lang)) {
            $lang = self::resolveLanguageIdentent($lang);

            if ($this->hasLanguage($lang)) {
                $this->currentLanguage = $lang;
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Invalid language: "%s"',
                    (string)$lang
                ));
            }
        } else {
            $this->currentLanguage = null;
        }

        return $this;
    }
}
