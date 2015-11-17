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
    private $languages;

    /**
    * @var string $default_lang
    */
    private $default_lang;

    /**
    * Current language
    * @var string $lang
    */
    private $lang;

    /**
    * @param array $data
    * @return TranslationConfig Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['languages']) && $data['languages'] !== null) {
            $this->set_available_langs($data['languages']);
        }
        if (isset($data['default_lang']) && $data['default_lang'] !== null) {
            $this->set_default_lang($data['default_lang']);
        }
        return $this;
    }

    /**
    * {@inheritdoc}
    */
    public function default_data()
    {
        return [
            'languages'    => [ 'en' ],
            'default_lang' => 'en'
        ];
    }

    /**
    * @param string $lang;
    * @throws InvalidArgumentException
    * @return TranslationString Chainable
    */
    public function set_lang($lang)
    {
        if (!in_array($lang, $this->available_langs())) {
            throw new InvalidArgumentException('Invalid language: "' . (string)$lang . '"');
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
            throw new InvalidArgumentException('Invalid language: "' . (string)$lang . '"');
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
    * Set the list of available languages.
    *
    * When updating the list of available languages, the default language
    * is checked against the new list. If the default language doesn't
    * exist in the new list, the first of the new available set is used.
    *
    * @param array $languages
    * @throws InvalidArgumentException
    * @return TranlsationConfig (Chainable)
    */
    public function set_available_langs($languages)
    {
        if (is_array($languages)) {
            $this->languages = array_values($languages);
            if (count($this->languages)) {
                if ($this->default_lang() && !in_array($this->default_lang(), $this->available_langs())) {
                    $this->set_default_lang(reset($languages));
                }
            }
        } else {
            throw new InvalidArgumentException('Must be an array of language codes.');
        }
        return $this;
    }

    /**
    * Get the list (array) of all available languages.
    *
    * @return array
    */
    public function available_langs()
    {
        return $this->languages;
    }
}
