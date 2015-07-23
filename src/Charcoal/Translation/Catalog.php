<?php

namespace Charcoal\Translation;

// Dependencies from `PHP`
use \ArrayAccess as ArrayAccess;
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Config\ConfigurableInterface as ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait as ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\Translation\CatalogInterface as CatalogInterface;

/**
*
*/
class Catalog implements
    CatalogInterface,
    ConfigurableInterface,
    ArrayAccess
{
    use ConfigurableTrait;

    /**
    * The array of translations, as a $lang => $val hash.
    * @var array $_translation_map
    */
    private $_translation_map = [];

    /**
    * Current language
    * @var string $_lang
    */
    private $_lang;

    /**
    * ArrayAccess -> offsetExists()
    * Called when using the objects as `isset($obj['offset'])`
    * @param string $offset
    * @throws InvalidArgumentException
    * @return boolean
    */
    public function offsetExists($offset)
    {
        if (!is_string($offset)) {
            throw new InvalidArgumentException('Can not get numeric array keys. Use string for array acccess.');
        } else {
            return isset($this->_translation_map[$offset]);
        }
    }

    /**
    * ArrayAccess -> offsetGet()
    * Get the translated string, in the current language.
    *
    * @param string $offset
    * @throws InvalidArgumentException
    * @return string
    */
    public function offsetGet($offset)
    {
        if (!is_string($offset)) {
            throw new InvalidArgumentException('Can not get numeric array keys. Use string for array acccess.');
        }
        return $this->tr($offset);
    }

    /**
    * ArrayAccess -> offsetSet()
    * Called when using $
    * @param string $offset
    * @param mixed $value
    * @throws InvalidArgumentException
    * @return void
    */
    public function offsetSet($offset, $value)
    {
        if (!is_string($offset)) {
            throw new InvalidArgumentException('Can not set numeric array keys. Use string for array acccess.');
        }
        if (is_array($value)) {
            $this->add_translation($offset, $value);
        } elseif (is_string($value)) {
             $this->add_translation_lang($offset, $value);
        } else {
            throw new InvalidArgumentException('Invalid value argument.');
        }

    }
    /**
    * ArrayAccess -> offsetUnset()
    * Called when using `unset($obj[$offset]);`
    * @throws InvalidArgumentException
    * @return void
    */
    public function offsetUnset($offset)
    {
        if (!is_string($offset)) {
            throw new InvalidArgumentException('Can not set numeric array keys. Use string for array acccess.');
        }

        if (isset($this->_translation_map[$offset])) {
            unset($this->_translation_map[$offset]);
        }
    }

    /**
    * Add a translation resource to the catalog.
    *
    * @param ResourceInterface|array|string $resource
    * @throws InvalidArgumentException
    * @return CatalogInterface Chainable
    */
    public function add_resource($resource)
    {
        if ($resource instanceof ResourceInterface) {
            throw new InvalidArgumentException('Soon');
        } elseif (is_array($resource)) {
            foreach ($resource as $ident => $translations) {
                $this->add_translation($ident, $translations);
            }
        } elseif (is_string($resource)) {
            // Try to load the current resource
            throw new InvalidArgumentException('String resource not (yet) supported');
        }

        return $this;
    }

    /**
    * Add a translation to the catalog
    *
    * @param string $ident
    * @param TranslationStringInterface|array $translations
    * @throws InvalidArgumentException
    * @return CatalogInterface Chainable
    */
    public function add_translation($ident, $translations)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException('Ident must be a string');
        }
        if ($translations instanceof TranslationStringInterface) {
            $translations = $translations->all();
        }

        if (!is_array($translations)) {
            throw new InvalidArgumentException('Translations must be an array or a StringInterface object');
        }
        foreach ($translations as $lang => $str) {
            $this->add_translation_lang($ident, $str, $lang);
        }
        return $this;
    }

    /**
    * @param string $ident
    * @param string $translation
    * @param string|null $lang
    * @throws InvalidArgumentException
    * @return Caltaog Chainable
    *
    */
    public function add_translation_lang($ident, $translation, $lang = null)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException('Ident must be a string');
        }
        if (!is_string($translation)) {
            throw new InvalidArgumentException('Tranlsation must be a string');
        }
        if ($lang === null) {
            $lang = $this->lang();
        }
        if (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException('Invalid lang');
        }

        if (isset($this->_translation_map[$ident])) {
            $this->_translation_map[$ident][$lang] = $translation;
        } else {
            $this->_translation_map[$ident] = [$lang=>$translation];
        }
        return $this;
    }

    /**
    * Get the full arrays
    * Optionally filter by language.
    *
    * @param string|null $lang (Optional) If set, discard results where lang is not set.
    * @throws InvalidArgumentException
    * @return array
    */
    public function available_translations($lang = null)
    {
        if ($lang===null) {
            return array_keys($this->_translation_map);
        }

        if (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException('Invalid lang');
        }
        $ret = [];
        foreach ($this->_translation_map as $ident => $translations) {
            if (isset($translations[$lang])) {
                $ret[] = $ident;
            }
        }
        return $ret;
    }

    /**
    * @param string $ident
    * @param string|null $lang
    * @throws InvalidArgumentException
    * @return string
    */
    public function tr($ident, $lang = null)
    {
        if ($lang === null) {
            $lang = $this->lang();
        }
        if (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException('Invalid lang');
        }
        if (isset($this->_translation_map[$ident])) {
            if (isset($this->_translation_map[$ident][$lang])) {
                return $this->_translation_map[$ident][$lang];
            } else {
                return $ident;
            }
        } else {
            return $ident;
        }
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
    * ConfigurableTrait > create_config()
    *
    * @return
    */
    private function create_config()
    {
        // Get the latest created instance of the config.
        return new TranslationConfig();
    }
}
