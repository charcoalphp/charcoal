<?php

namespace Charcoal\Translator;

use Symfony\Component\Translation\Translator as SymfonyTranslator;

use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translation;

/**
 * Extends the symfony translator to allow returned values in a "Translation" oject,
 * containing localizations for all locales.
 *
 */
class Translator extends SymfonyTranslator
{
    /**
     * @var LocalesManager
     */
    private $manager;

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

        parent::__construct(
            $data['locale'],
            $data['message_selector'],
            $data['cache_dir'],
            $data['debug']
        );
    }

    /**
     * Get a translation object from a (mixed) value.
     *
     * @param mixed $val The string or translation-object to retrieve.
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
                $translation[$lang] = $this->trans($val, [], null, $lang);
            }
        }
        return $translation;
    }

    /**
     * Get a translated string from a (mixed) value.
     *
     * @param mixed $val The string or translation-object to retrieve.
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
     * @return string[]
     */
    public function locales()
    {
        return $this->manager->locales();
    }

    /**
     * @return string[]
     */
    public function availableLocales()
    {
        return $this->manager->availableLocales();
    }

        /**
     * Ensure that the `setLocale()` method also changes the locales manager's language.
     *
     * @see SymfonyTranslator::setLocale()
     * @param string $locale The locale ident (language) to set.
     * @return void
     */
    public function setLocale($locale)
    {
        parent::setLocale($locale);
        $this->manager->setCurrentLocale($locale);
    }

    /**
     * @param LocalesManager $manager The Locales manager.
     * @return void
     */
    private function setManager(LocalesManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param mixed $val The value to be checked.
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
