<?php

namespace Charcoal\Translation;

// Dependencies from `PHP`
use \ArrayAccess as ArrayAccess;
use \Exception as Exception;
use \InvalidArgumentException;

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
    \ArrayAccess
{
    use ConfigurableTrait;

    /**
    * The language translations array.
    * Stored as a $lang=>$val hash.
    * @var array $val
    */
    private $_val = [];

    /**
    * @var string $_lang
    */
    private $_lang = null;

    /**
    * Calling the constructor with a parameter should force setting it up as value.
    *
    * @param TranslationString|array|string $val
    */
    public function __construct($val = null)
    {
        if ($val !== null) {
            $this->set_val($val);
        }
    }

    /**
    * Magic caller.
    *
    * Accepts language as a method:
    * ```php
    * $str = new TranslationString(['en'=>'foo', 'fr'=>'bar']);
    * // Because "fr" is an available language, this will output "bar".
    * echo $str-fr();
    * ```
    *
    * @param string $method
    * @throws Exception
    * @return string
    */
    public function __call($method, $args = null)
    {
        unset($args);
        if (in_array($method, $this->available_langs())) {
            $lang = $method;
            return $this->val($lang);
        } else {
            throw new Exception('Invalid lang');
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
    * Accept 3 types of arguments:
    * - object (TranslationStringInterface): The data will be copied from object's
    * - array: All languages available in the array
    *   - The format of the array should be a hash in the `lang` => `string` format.
    * - string: The value will be assigned to the current language
    *
    * @param TranslationString|array|string $val
    * @throws InvalidArgumentException
    * @return TranslationString Chainable
    */
    public function set_val($val)
    {
        if ($val instanceof TranslationString) {
            $this->_val = $val->all();
        } elseif (is_array($val)) {
            $this->_val = [];
            foreach ($val as $lang => $l10n) {
                $this->add_val($lang, $l10n);
            }
        } elseif (is_string($val)) {
            // Set as default lang
            $this->_val[$this->lang()] = $val;
        } else {
            throw new InvalidArgumentException('Invalid L10n value');
        }
        return $this;
    }

    /**
    * @param string $lang
    * @param string $val
    * @throws InvalidArgumentException
    * @return TranslationString Chainable
    */
    public function add_val($lang, $val)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException('Lang must be a string.');
        }
        if (!is_string($val)) {
            throw new InvalidArgumentException('L10n value must be a string.');
        }
        if (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException('Invalid lang');
        }
        $this->_val[$lang] = $val;
        return $this;
    }


    /**
    * Get the the translation values.
    *
    * @param string|null $lang
    * @throws InvalidArgumentException
    * @return string
    */
    public function val($lang = null)
    {
        if ($lang === null) {
            $lang = $this->lang();
        } elseif (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException('Invalid lang');
        }

        if (isset($this->_val[$lang]) && $this->_val[$lang] !== null) {
            return $this->_val[$lang];
        } else {
            $l = $this->default_lang();
            if (isset($this->_val[$l]) && $this->_val[$l] !== null) {
                return $this->_val[$l];
            } else {
                return '';
            }
        }
    }

    /**
    * Get the array of values in all langueages
    * @return array
    */
    public function all()
    {
        return $this->_val;
    }

    /**
    * @param string $lang
    * @throws InvalidArgumentException
    * @return TranslationString Chainable
    */
    public function set_lang($lang)
    {
        if (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException('Invalid lang');
        }
        $this->_lang = $lang;
        return $this;
    }

    /**
    * Get the actual language (Set in either the object or the dfault configuration)
    * Typically from config / session.
    * @return string
    */
    public function lang()
    {
        if (!$this->_lang) {
            $this->_lang = $this->default_lang();
        }
        return $this->_lang;
    }

    /**
    * Get the default language (used when none is set/specified).
    * Typicaly from config.
    * @return string
    */
    private function default_lang()
    {
        $translation_config = $this->config();
        return $translation_config->lang();
    }

    /**
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
    * @return
    */
    private function create_config()
    {
        // Get the latest created instance of the config.
        return new TranslationConfig();
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
        if (isset($this->_val[$key])) {
            unset($this->_val[$key]);
        }
    }
}
