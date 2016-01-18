<?php

namespace Charcoal\Translation;

use \ArrayAccess;
use \ArrayIterator;
use \Exception;
use \InvalidArgumentException;
use \IteratorAggregate;
use \JsonSerializable;
use \Serializable;

// Intra-module (`charcoal-config`) dependencies
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\Translation\ConfigurableTranslationTrait;
use \Charcoal\Translation\MultilingualAwareInterface;
use \Charcoal\Translation\TranslationConfig;
use \Charcoal\Translation\TranslationStringInterface;

/**
 * Translation String Object
 *
 * Allow a string to be translatable, transparently.
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
 * @see \Charcoal\Translation\Catalog for a similar exception for the current language.
 */
class TranslationString implements
    JsonSerializable,
    Serializable,
    MultilingualAwareInterface,
    TranslationStringInterface,
    ConfigurableInterface,
    ArrayAccess,
    IteratorAggregate
{
    use ConfigurableTrait;
    use ConfigurableTranslationTrait;

    /**
     * The object's translations
     *
     * Stored as a `[ $lang => $val ]` hash.
     *
     * @var array $val
     */
    private $val = [];

    /**
     * Current language identifier.
     *
     * @var string
     */
    private $currentLanguage;

    /**
     * Calling the constructor with a parameter should force setting it up as value.
     *
     * @param  mixed                   $val One or more strings (as an array).
     * @param  TranslationConfig|array $config An existing TranslationConfig or settings to apply to this instance.
     * @return self
     */
    public function __construct($val = null, $config = null)
    {
        if (isset($config)) {
            $this->setConfig($config);
        }

        if (isset($val)) {
            $this->setVal($val);
        }

        return $this;
    }

    /**
     * Magic caller.
     *
     * Accepts language as a method:
     *
     * ```php
     * $str = new TranslationString([ 'en' => 'foo', 'fr' => 'bar' ]);
     * // Because "fr" is an available language, this will output "bar".
     * echo $str->fr();
     * ```
     *
     * @param  string $method A language for an available translation.
     * @return string A translated string.
     * @throws Exception If language isn't available.
     */
    public function __call($method, $args = null)
    {
        unset($args);

        if (in_array($method, $this->languages())) {
            return $this->val($method);
        } else {
            throw new Exception(
                sprintf('Invalid language: "%s"', (string)$method)
            );
        }
    }

    /**
     * Magic string getter, when the object is cast as a string.
     *
     * This allows, amongst other things, to use the `TranslationString`
     * object directly in a mustache template.
     *
     * @return string The translated string, in current language.
     */
    public function __toString()
    {
        return $this->val();
    }

    /**
     * Assign the current translation value(s).
     *
     * @param TranslationStringInterface|array|string $val
     *     Add one or more translation values.
     *
     *     Accept 3 types of arguments:
     *     - object (TranslationStringInterface): The data will be copied from the object's.
     *     - array: All languages available in the array. The format of the array should
     *       be a hash in the `lang` => `string` format.
     *     - string: The value will be assigned to the current language.
     * @return self
     * @throws InvalidArgumentException If value is invalid.
     */
    public function setVal($val)
    {
        if ($val instanceof TranslationStringInterface) {
            $this->val = $val->all();
        } elseif (is_array($val)) {
            $this->val = [];

            foreach ($val as $lang => $l10n) {
                $this->addVal($lang, $l10n);
            }
        } elseif (is_string($val)) {
            $lang = $this->currentLanguage();

            $this->val[$lang] = $val;
        } else {
            throw new InvalidArgumentException(
                'Invalid localized value.'
            );
        }
        return $this;
    }

    /**
     * Add a translation value to a specified and available language.
     *
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @param  string                   $val  The translation to be added.
     * @return self
     * @throws InvalidArgumentException If the language or value is invalid.
     */
    public function addVal($lang, $val)
    {
        $lang = self::resolveLanguageIdent($lang);

        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Language code must be a string or an instance of LanguageInterface.'
            );
        }

        if (!is_string($val)) {
            throw new InvalidArgumentException('Localized value must be a string.');
        }

        $this->val[$lang] = $val;

        return $this;
    }

    /**
     * Remove a translation value specified by an available language.
     *
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return self
     * @throws InvalidArgumentException If language is invalid.
     */
    public function removeVal($lang)
    {
        $lang = self::resolveLanguageIdent($lang);

        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Must be a string-cast language code or an instance of LanguageInterface.'
            );
        }

        if ($this->hasVal($lang)) {
            unset($this->val[$lang]);
        }
        return $this;
    }

    /**
     * Get a translation value.
     *
     * Returns a translation in the current language.
     *
     * If $lang is provided, that language's translation is returned.
     * If $lang isn't a supported language or the translation is unavailable,
     * the translation in the default language is returned.
     *
     * @param  LanguageInterface|string|null $lang Optional supported language to retrieve a translation in.
     * @return string
     * @throws InvalidArgumentException If language is invalid.
     * @todo   When the language is invalid, should we fallback to the default language
     *         or throw an InvalidArgumentException.
     */
    public function val($lang = null)
    {
        if ($lang === null) {
            $lang = $this->currentLanguage();
        } elseif (!$this->hasLanguage($lang)) {
            throw new InvalidArgumentException(sprintf('Invalid language: "%s"', (string)$lang));
        }

        if ($this->hasVal($lang)) {
            return $this->val[$lang];
        } else {
            $lang = $this->defaultLanguage();

            if ($this->hasVal($lang)) {
                return $this->val[$lang];
            } else {
                return '';
            }
        }
    }

    /**
     * Determine if the object has a specified translation.
     *
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return boolean
     * @throws InvalidArgumentException If language is invalid.
     */
    public function hasVal($lang)
    {
        $lang = self::resolveLanguageIdent($lang);

        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Must be a string-cast language code or an instance of LanguageInterface.'
            );
        }

        return isset($this->val[$lang]);
    }

    /**
     * Alias of `self::hasVal()`.
     *
     * @param  LanguageInterface|string $lang A language object or identifier.
     * @return boolean
     */
    public function hasTranslation($lang)
    {
        return $this->hasVal($lang);
    }

    /**
     * Get the array of translations in all languages.
     *
     * @return string[]
     *
     * @todo Add support for retrieving a subset of translations.
     */
    public function all()
    {
        return $this->val;
    }

    /**
     * Get an array of translations in either all languages or a select few.
     *
     * @param  (LanguageInterface|string)[] $langs
     *     If an array of one or more lanagues is provided, the method returns
     *     a subset of the object's available languages (if any).
     * @return (LanguageInterface|string)[] An array of available languages.
     */
    public function translations(array $langs = [])
    {
        if (count($langs)) {
            array_walk($langs, function (&$val, $key) {
                $val = self::resolveLanguageIdent($val);
            });

            return array_intersect_key($this->all(), array_flip($langs));
        }

        return $this->all();
    }

    /**
     * Alias of `ConfigurableTranslationTrait::hasLanguage()`.
     *
     * Called when using the objects as `isset($obj['foo'])`.
     *
     * @see    ArrayAccess::offsetExists()
     * @param  string $lang A language identifier.
     * @return boolean
     * @throws InvalidArgumentException If array key isn't a string.
     */
    public function offsetExists($lang)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Array key must be a string.'
            );
        }

        return $this->hasVal($lang);
    }

    /**
     * Alias of `self::val()`.
     *
     * @see    ArrayAccess::offsetGet()
     * @param  string $lang A language identifier.
     * @return string A translated string.
     * @throws InvalidArgumentException If array key isn't a string.
     */
    public function offsetGet($lang)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Array key must be a string.'
            );
        }

        return $this->val($lang);
    }

    /**
     * Alias of `self::addVal()`.
     *
     * @see    ArrayAccess::offsetSet()
     * @param  string $lang A language identifier.
     * @param  string $val  A translation value.
     * @return void
     * @throws InvalidArgumentException If array key isn't a string.
     */
    public function offsetSet($lang, $val)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Array key must be a string.'
            );
        }

        $this->addVal($lang, $val);
    }

    /**
     * Alias of `self::removeVal()`.
     *
     * Called when using `unset($obj['foo']);`.
     *
     * @see    ArrayAccess::offsetUnset()
     * @param  string $lang A language identifier.
     * @return void
     * @throws InvalidArgumentException If array key isn't a string.
     */
    public function offsetUnset($lang)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Array key must be a string.'
            );
        }

        $this->removeVal($lang);
    }

    /**
     * Retrieve an external iterator of translations in all languages.
     *
     * @see    IteratorAggregate::getIterator()
     * @uses   self::all()
     * @return array
     */
    public function getIterator()
    {
        return new ArrayIterator($this->all());
    }

    /**
     * Get the config's default language.
     *
     * @uses   ConfigurableInterface::config()
     * @return string A language identifier.
     */
    public function defaultLanguage()
    {
        return $this->config()->defaultLanguage();
    }

    /**
     * Get the object's current language.
     *
     * The current language acts as the first to be used when interacting
     * with data in a context where the language isn't explicitly specified.
     *
     * @see    TranslatableTrait::currentLanguage()
     * @return string A language identifier.
     */
    public function currentLanguage()
    {
        if (!isset($this->currentLanguage)) {
            return 'fr';
            //return $this->config()->currentLanguage();
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
     * @param  LanguageInterface|string|null $lang A language object or identifier.
     * @return self
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

    /**
    * Serializable > serialize()
    */
    public function serialize()
    {
        $data = $this->all();
        return serialize($data);
    }
    /**
    * Serializable > unsierialize()
    *
    * @param string $data Serialized data
    * @return void
    */
    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->setVal($data);
    }

    /**
    * JsonSerializable > jsonSerialize()
    */
    public function jsonSerialize()
    {
        return $this->all();
    }
}
