<?php

namespace Charcoal\Translation;

// Intra-module (`charcoal-app`) dependency
use \Charcoal\App\Language\LanguageInterface;

/**
 * An implementation of the `LanguageAwareInterface`.
 *
 * A basic trait for objects that simply need to track a single language value.
 */
trait LanguageAwareTrait
{
    /**
     * Current language identifier.
     *
     * @var string
     */
    private $language;

    /**
     * Retrieve the object's current language.
     *
     * @return string A language identifier.
     */
    public function language()
    {
        return $this->language;
    }

    /**
     * Set the object's current language.
     *
     * @param  LanguageInterface|string|null $lang A language object or identifier.
     * @return self
     */
    public function setLanguage($lang = null)
    {
        if (isset($lang)) {
            if ($lang instanceof LanguageInterface) {
                $lang = (string)$lang->ident();
            } elseif (is_array($lang) && isset($lang['ident'])) {
                $lang = (string)$lang['ident'];
            }

            $this->language = $lang;
        } else {
            $this->language = null;
        }

        return $this;
    }
}
