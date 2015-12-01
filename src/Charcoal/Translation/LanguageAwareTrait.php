<?php

namespace Charcoal\Translation;

// Intra-module (`charcoal-app`) dependency
use \Charcoal\App\Language\LanguageInterface;

/**
 * A basic trait for objects that simply need to track a single language value.
 */
trait LanguageAwareTrait
{
    /**
     * Current language identifier
     *
     * @var string
     */
    private $language;

    /**
     * Get the object's current language
     *
     * The current language acts as the first to be used when interacting
     * with data in a context where the language isn't explicitly specified.
     *
     * @return string  A language identifier
     */
    public function language()
    {
        return $this->language;
    }

    /**
     * Set the object's current language.
     *
     * Must be one of the available languages assigned to the object.
     *
     * Defaults to resetting the object's current language to NULL,
     * (which falls onto the default language).
     *
     * @param  LanguageInterface|string|null  $lang  A language object or identifier
     * @return self
     *
     * @throws InvalidArgumentException if language isn't available
     */
    public function set_language($lang = null)
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
