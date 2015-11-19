<?php

namespace Charcoal\Translation;

// Dependencies from `PHP`
use \ArrayAccess;
use \ArrayIterator;
use \Exception;
use \InvalidArgumentException;
use \IteratorAggregate;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\Translation\TranslationStringInterface;

/**
* Translation String object
*
* Allow a string to be translatable, transparently.
*/
class TranslationString implements
    TranslationStringInterface,
    ConfigurableInterface,
    ArrayAccess,
    IteratorAggregate
{
    use ConfigurableTrait;

    /**
    * The object's translations
    *
    * Stored as a `$lang => $val` hash.
    *
    * @var array $val
    */
    private $val = [];

    /**
    * The object's current language
    *
    * @var string $lang
    */
    private $lang = null;

    /**
    * Calling the constructor with a parameter should force setting it up as value.
    *
    * @param  TranslationStringInterface|array|string $val
    * @param  TranslationConfig|array                 $config
    * @return self
    */
    public function __construct($val = null, $config = null)
    {
        if ($config !== null) {
            $this->set_config($config);
        }
        if ($val !== null) {
            $this->set_val($val);
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
    * @param  string $method
    * @return string
    * @throws Exception
    */
    public function __call($method, $args = null)
    {
        unset($args);
        if (in_array($method, $this->available_langs())) {
            return $this->val($method);
        } else {
            throw new Exception(sprintf('Invalid language: "%s"', (string)$method));
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
        $lang = $this->lang();
        return $this->val($lang);
    }

    /**
    * Set the current translation value(s)
    *
    * @param TranslationStringInterface|array|string $val {
    *     Add one or more translation values.
    *
    *     Accept 3 types of arguments:
    *     - object (TranslationStringInterface): The data will be copied from the object's.
    *     - array: All languages available in the array. The format of the array should
    *       be a hash in the `lang` => `string` format.
    *     - string: The value will be assigned to the current language.
    * }
    * @return self
    * @throws InvalidArgumentException
    */
    public function set_val($val)
    {
        if ($val instanceof TranslationStringInterface) {
            $this->val = $val->all();
        } elseif (is_array($val)) {
            $this->val = [];
            foreach ($val as $lang => $l10n) {
                $this->add_val($lang, $l10n);
            }
        } elseif (is_string($val)) {
            // Set as default lang
            $this->val[$this->lang()] = $val;
        } else {
            throw new InvalidArgumentException('Invalid localized value.');
        }
        return $this;
    }

    /**
    * Add a translation value to a specified, and available, language
    *
    * @param  string $lang An available language identifier
    * @param  string $val  The translation to be added
    * @return self
    * @throws InvalidArgumentException
    */
    public function add_val($lang, $val)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException('Language must be a string.');
        }
        if (!is_string($val)) {
            throw new InvalidArgumentException('Localized value must be a string.');
        }
        if (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException(sprintf('Invalid language: "%s"', (string)$lang));
        }
        $this->val[$lang] = $val;
        return $this;
    }


    /**
    * Get a translation value
    *
    * Returns a translation in the current language.
    *
    * If $lang is provided, that language's translation is returned.
    * If $lang isn't a supported language or the translation is unavailable,
    * the translation in the default language is returned.
    *
    * @param  string|null $lang Optional supported language to retrieve a translation in.
    * @return string
    * @throws InvalidArgumentException
    */
    public function val($lang = null)
    {
        if ($lang === null) {
            $lang = $this->lang();
        } elseif (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException(sprintf('Invalid language: "%s"', (string)$lang));
        }

        if (isset($this->val[$lang]) && $this->val[$lang] !== null) {
            return $this->val[$lang];
        } else {
            $l = $this->default_lang();
            if (isset($this->val[$l]) && $this->val[$l] !== null) {
                return $this->val[$l];
            } else {
                return '';
            }
        }
    }

    /**
    * Get the array of translations in all languages
    *
    * @return array
    *
    * @todo Add support for retrieving a subset of translations.
    */
    public function all()
    {
        return $this->val;
    }

    /**
    * Set the current object's language
    *
    * @see    TranslationConfig::set_lang() for another copy of this method
    * @param  string $lang The current language
    * @return self
    * @throws InvalidArgumentException
    */
    public function set_lang($lang)
    {
        if (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException(sprintf('Invalid language: "%s"', (string)$lang));
        }
        $this->lang = $lang;
        return $this;
    }

    /**
    * Get the current object's language
    *
    * @see    TranslationConfig::lang() for another copy of this method
    * @return string If none was set, returns the object's default language.
    *
    * @todo Add support for retrieving the current language
    *       from the project config or the client session.
    */
    public function lang()
    {
        if (!$this->lang) {
            $this->lang = $this->default_lang();
        }
        return $this->lang;
    }

    /**
    * Get the object's default language
    *
    * @return string
    */
    private function default_lang()
    {
        $translation_config = $this->config();
        return $translation_config->lang();
    }

    /**
    * Get the object's list of available languages
    *
    * @return array
    */
    private function available_langs()
    {
        $translation_config = $this->config();
        return $translation_config->available_langs();
    }

    /**
    * ConfigurableInterface > create_config()
    *
    * @see    Catalog::create_config() for another copy of this method
    * @param  array $data Optional
    * @return TranslationConfig
    *
    * @todo   Get the latest created instance of the config.
    */
    private function create_config(array $data = null)
    {
        $config = new TranslationConfig();
        if ($data !== null) {
            $config->set_data($data);
        }
        return $config;
    }

    /**
    * ArrayAccess > offsetGet
    *
    * @param string $key
    * @return string
    */
    public function offsetGet($key)
    {
        return $this->val($key);
    }

    /**
    * ArrayAccess > offsetSet
    *
    * @param string $key
    * @param string $val
    * @return void
    */
    public function offsetSet($key, $val)
    {
        $this->add_val($key, $val);
    }

    /**
    * ArrayAccess > offsetExists
    *
    * @param string $key
    * @return boolean
    */
    public function offsetExists($key)
    {
        return in_array($key, $this->available_langs());
    }

    /**
    * ArrayAccess > offsetUnset
    *
    * @param string $key
    * @return void
    */
    public function offsetUnset($key)
    {
        if (isset($this->val[$key])) {
            unset($this->val[$key]);
        }
    }

    /**
    * IteratorAggregate > getIterator
    *
    * @return array
    */
    public function getIterator()
    {
        $iterator = new ArrayIterator($this->val);
        return $iterator;
    }
}
