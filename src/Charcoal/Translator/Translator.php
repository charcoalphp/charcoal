<?php

namespace Charcoal\Translator;

use Symfony\Component\Translation\Translator as SymfonyTranslator;

use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translation;

/**
 * Charcoal Translator.
 *
 * Extends the Symfony translator to allow returned values in a "Translation" oject,
 * containing localizations for all locales.
 */
class Translator extends SymfonyTranslator
{
    /**
     * The locales manager.
     *
     * @var LocalesManager
     */
    private $manager;

    /**
     * The loaded domains.
     *
     * @var string[]
     */
    private $domains = [ 'messages' ];

    /**
     * @param array $data Constructor data.
     * @return
     */
    public function __construct(array $data)
    {
        $this->setManager($data['manager']);

        $defaults = [
            'locale'            => $this->manager->currentLocale(),
            'message_selector'  => null,
            'cache_dir'         => null,
            'debug'             => false
        ];
        $data = array_merge($defaults, $data);

        // If symfony-config is not installed, DON'T use cache.
        if (!class_exists('\Symfony\Component\Config\ConfigCacheFactory')) {
            $data['cache_dir'] = null;
        }

        parent::__construct(
            $data['locale'],
            $data['message_selector'],
            $data['cache_dir'],
            $data['debug']
        );
    }

    /**
     * Adds a resource.
     *
     * @see    SymfonyTranslator::addResource() Keep track of the translation domains.
     * @param  string      $format   The name of the loader (@see addLoader()).
     * @param  mixed       $resource The resource name.
     * @param  string      $locale   The locale.
     * @param  string|null $domain   The domain.
     * @return void
     */
    public function addResource($format, $resource, $locale, $domain = null)
    {
        if (null !== $domain) {
            $this->domains[] = $domain;
        }

        parent::addResource($format, $resource, $locale, $domain);
    }

    /**
     * Retrieve the loaded domains.
     *
     * @return string[]
     */
    public function availableDomains()
    {
        return $this->domains;
    }

    /**
     * Get a translation object from a (mixed) value.
     *
     * @param  mixed $val The string or translation-object to retrieve.
     * @return Translation|null
     */
    public function translation($val)
    {
        if ($this->isValidTranslation($val) === false) {
            return null;
        }
        $translation = new Translation($val, $this->manager);
        foreach ($this->availableLocales() as $lang) {
            if (!isset($translation[$lang]) || $translation[$lang] == $val) {
                $translation[$lang] = $this->trans((string)$translation, [], null, $lang);
            }
        }
        return $translation;
    }

    /**
     * Get a translated string from a (mixed) value.
     *
     * @param  mixed $val The string or translation-object to retrieve.
     * @return string
     */
    public function translate($val)
    {
        if (is_string($val)) {
            return $this->trans($val);
        } else {
            $translation = $this->translation($val);
            return (string)$translation;
        }
    }

    /**
     * Retrieve the available locales information.
     *
     * @return array
     */
    public function locales()
    {
        return $this->manager->locales();
    }

    /**
     * Retrieve the available locales (language codes).
     *
     * @return string[]
     */
    public function availableLocales()
    {
        return $this->manager->availableLocales();
    }

    /**
     * Sets the current locale.
     *
     * @see    SymfonyTranslator::setLocale() Ensure that the method also changes the locales manager's language.
     * @param  string $locale The locale.
     * @return void
     */
    public function setLocale($locale)
    {
        parent::setLocale($locale);

        $this->manager->setCurrentLocale($locale);
    }

    /**
     * Set the locales manager.
     *
     * @param  LocalesManager $manager The locales manager.
     * @return void
     */
    private function setManager(LocalesManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Determine if the value is translatable.
     *
     * @param  mixed $val The value to be checked.
     * @return boolean
     */
    private function isValidTranslation($val)
    {
        if ($val === null) {
            return false;
        }

        if (is_string($val)) {
            return !empty(trim($val));
        }

        if ($val instanceof Translation) {
            return true;
        }

        if (is_array($val)) {
            return !!array_filter(
                $val,
                function ($v, $k) {
                    if (is_string($k) && is_string($v)) {
                        if (strlen($k) > 0) {
                            return true;
                        }
                    }

                    return false;
                },
                ARRAY_FILTER_USE_BOTH
            );
        }
        return false;
    }
}
