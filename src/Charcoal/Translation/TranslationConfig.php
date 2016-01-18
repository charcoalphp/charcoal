<?php

namespace Charcoal\Translation;

use \InvalidArgumentException;

// Intra-module (`charcoal-app`) dependency
use \Charcoal\App\App as CharcoalApp;

// Intra-module (`charcoal-config`) dependency
use \Charcoal\Config\AbstractConfig;

// Intra-module (`charcoal-core`) dependency
use \Charcoal\Charcoal;

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
     * Get the default configuration
     *
     * @return array
     */
    public function defaults()
    {
        return [
            'languages' => [
                'en' => [
                    'name' => 'English'
                ],
                'fr' => [
                    'name' => 'Français'
                ]
            ]
        ];
    }

    /**
     * Retrieve a Charcoal application's instance or a new instance of self.
     *
     * A TranslationConfig is retrieved from the {@see CharcoalApp}'s LanguageManager (if any).
     *
     * @see    AbstractSource::add_filter() Similar implementation.
     * @see    AbstractProperty::fields() Similar implementation.
     *
     * @see    Charcoal\App\Language\LanguageManager For application-wide source of instance returned.
     * @see    ConfigurableInterface::create_config() Similar method.
     * @param  array|string|null $data Optional data to pass to the new TranslationConfig instance.
     * @return TranslationConfig
     */
    public static function instance($data = null)
    {
        if ($data === null && method_exists('\Charcoal\App\App', 'instance')) {
            $app = CharcoalApp::instance();

            return $app->languageManager()->config();
        }

        return new self($data);
    }
}
