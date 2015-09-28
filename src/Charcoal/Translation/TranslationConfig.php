<?php

namespace Charcoal\Translation;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Config\AbstractConfig;

/**
* Global Translation configuration.
*/
class TranslationConfig extends AbstractConfig
{
    /**
    * All available languages
    * @var array $languages
    */
    private $languages = [];

    /**
    * @var string $default_lang
    */
    private $default_lang = 'en';

    /**
    * Current language
    * @var string $lang
    */
    private $lang = null;

    /**
    * @param array $data
    * @return TranslationConfig Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['languages']) && $data['languages'] !== null) {
            $this->set_languages($data['languages']);
        }
        if (isset($data['default_lang']) && $data['default_lang'] !== null) {
            $this->set_default_lang($data['default_lang']);
        }

        return $this;
    }

    /**
    * @param string $lang;
    * @throws InvalidArgumentException
    * @return TranslationString Chainable
    */
    public function set_lang($lang)
    {
        if (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException('Invalid lang');
        }
        $this->lang = $lang;
        return $this;
    }

    /**
    * Get the actual language (Set in either the object or the dfault configuration)
    * Typically from config / session.
    * @return string
    */
    public function lang()
    {
        if (!$this->lang) {
            $this->lang = $this->default_lang();
        }
        return $this->lang;
    }

    /**
    * Set the default language.
    *
    * @param string $lang
    * @throws InvalidArgumentException
    * @return TranlsationConfig (Chainable)
    */
    public function set_default_lang($lang)
    {
        if (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException('Invalid lang');
        }
        $this->default_lang = $lang;
        return $this;
    }

    /**
    * Get the default language.
    *
    * @return string
    */
    public function default_lang()
    {
        return $this->default_lang;
    }

    /**
    * Get the list (array) of all available languages.
    *
    * @return array
    */
    public function available_langs()
    {
        // @todo
        return ['en', 'fr'];
    }
}
