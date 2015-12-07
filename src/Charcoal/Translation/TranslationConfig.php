<?php

namespace Charcoal\Translation;

use \InvalidArgumentException;

// Intra-module (`charcoal-config`) dependency
use \Charcoal\Config\AbstractConfig;

// Local namespace dependencies
use \Charcoal\Translation\MultilingualAwareInterface;
use \Charcoal\Translation\TranslatableTrait;

/**
 * Configuration handler for translations, such as instances of TranslationStringInterface.
 */
class TranslationConfig extends AbstractConfig implements MultilingualAwareInterface
{
    use TranslatableTrait;

    /**
     * Set the configuration
     *
     * @param  array $data The data to set.
     * @return self
     */
    public function set_data(array $data)
    {
        if (isset($data['languages'])) {
            $this->set_languages($data['languages']);
        }

        if (isset($data['default_language'])) {
            $this->set_default_language($data['default_language']);
        }

        if (isset($data['current_language'])) {
            $this->set_current_language($data['current_language']);
        }

        return $this;
    }

    /**
     * Get the default configuration
     *
     * @return array
     */
    public function default_data()
    {
        return [
            'languages' => [
                'en' => [
                    'name' => 'English'
                ]
            ]
        ];
    }
}
