<?php

namespace Charcoal\Translator;

use ArrayAccess;
use DomainException;
use InvalidArgumentException;
use JsonSerializable;
// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;

/**
 * A translation object holds a localized message in all available locales.
 *
 * Available locales is provided with a locales manager.
 */
class Translation implements
    ArrayAccess,
    JsonSerializable
{
    /**
     * The object's translations.
     *
     * Stored as a `[ $lang => $val ]` hash.
     *
     * @var string[]
     */
    private $val = [];

    /**
     * @var LocalesManager
     */
    private $manager;

    /**
     * @param Translation|array|string $val     The translation values.
     * @param LocalesManager           $manager A LocalesManager instance.
     */
    public function __construct($val, LocalesManager $manager)
    {
        $this->manager = $manager;
        $this->setVal($val);
    }

    /**
     * Output the current language's value, when cast to string.
     *
     * @return string
     */
    public function __toString()
    {
        $lang = $this->manager->currentLocale();
        if (isset($this->val[$lang])) {
            return $this->val[$lang];
        } else {
            return '';
        }
    }

    /**
     * Get the array of translations in all languages.
     *
     * @return string[]
     */
    public function data()
    {
        return $this->val;
    }

    /**
     * @param  string $lang A language identifier.
     * @return boolean
     * @see    ArrayAccess::offsetExists()
     * @throws InvalidArgumentException If array key isn't a string.
     */
    public function offsetExists($lang)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid language; must be a string, received %s',
                (is_object($lang) ? get_class($lang) : gettype($lang))
            ));
        }

        return isset($this->val[$lang]);
    }

    /**
     * @param  string $lang A language identifier.
     * @return string A translated string.
     * @see    ArrayAccess::offsetGet()
     * @throws InvalidArgumentException If array key isn't a string.
     * @throws DomainException If the array key is not found.
     */
    public function offsetGet($lang)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid language; must be a string, received %s',
                (is_object($lang) ? get_class($lang) : gettype($lang))
            ));
        }

        if (!isset($this->val[$lang])) {
            throw new DomainException(sprintf(
                'Translation for "%s" is not defined.',
                $lang
            ));
        }

        return $this->val[$lang];
    }

    /**
     * @param  string $lang A language identifier.
     * @param  string $val  A translation value.
     * @return void
     * @see    ArrayAccess::offsetSet()
     * @throws InvalidArgumentException If array key isn't a string.
     */
    public function offsetSet($lang, $val)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid language; must be a string, received %s',
                (is_object($lang) ? get_class($lang) : gettype($lang))
            ));
        }

        if (!is_string($val)) {
            throw new InvalidArgumentException(sprintf(
                'Translation must be a string, received %s',
                (is_object($val) ? get_class($val) : gettype($val))
            ));
        }

        $this->val[$lang] = $val;
    }

    /**
     * @param  string $lang A language identifier.
     * @return void
     * @see    ArrayAccess::offsetUnset()
     * @throws InvalidArgumentException If array key isn't a string.
     */
    public function offsetUnset($lang)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid language; must be a string, received %s',
                (is_object($lang) ? get_class($lang) : gettype($lang))
            ));
        }

        unset($this->val[$lang]);
    }

    /**
     * Retrieve translations that can be serialized natively by json_encode().
     *
     * @return string[]
     * @see    JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->data();
    }

    /**
     * Transform each language's value using a callback.
     *
     * @param  callable $callback The callback function to run for each value.
     *     The callback takes on the value only.
     * @return self
     */
    public function sanitize(callable $callback)
    {
        foreach ($this->val as $lang => $val) {
            $this->val[$lang] = call_user_func($callback, $val);
        }
        return $this;
    }

    /**
     * Transform each language's value, with locale, using a callback.
     *
     * @param  callable $callback The callback function to run for each value.
     *     The callback takes on two parameters. The value being the first, and the language code second.
     * @return self
     */
    public function each(callable $callback)
    {
        foreach ($this->val as $lang => $val) {
            $this->val[$lang] = call_user_func($callback, $val, $lang);
        }
        return $this;
    }

    /**
     * Assign the current translation value(s).
     *
     * @param Translation|array|string $val The translation value(s).
     *     Add one or more translation values.
     *
     *     Accept 3 types of arguments:
     *     - object (TranslationInterface): The data will be copied from the object's.
     *     - array: All languages available in the array. The format of the array should
     *       be a hash in the `lang` => `string` format.
     *     - string: The value will be assigned to the current language.
     * @return self
     * @throws InvalidArgumentException If language or value are invalid.
     */
    private function setVal($val)
    {
        if ($val instanceof Translation) {
            $this->val = $val->data();
        } elseif (is_array($val)) {
            $this->val = [];
            foreach ($val as $lang => $l10n) {
                if (!is_string($lang)) {
                    throw new InvalidArgumentException(sprintf(
                        'Invalid language; must be a string, received %s',
                        (is_object($lang) ? get_class($lang) : gettype($lang))
                    ));
                }

                $this->val[$lang] = (string)$l10n;
            }
        } elseif (is_string($val)) {
            $lang = $this->manager->currentLocale();

            $this->val[$lang] = $val;
        } else {
            throw new InvalidArgumentException(
                'Invalid localized value.'
            );
        }

        return $this;
    }
}
