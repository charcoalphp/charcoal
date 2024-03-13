<?php

namespace Charcoal\Translator;

use ArrayAccess;
use DomainException;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Translatable Value
 *
 *  Alternative to {@see \Charcoal\Translator\Translation} that is not limited
 *  to strings; can support any kind of value.
 *
 *  Unlike `Translation`, this class has no construction dependencies.
 *  However, a `Translator` instance is required for retrieving the
 *  localized portion of the value.
 */
class TranslatableValue implements
    TranslatableInterface,
    ArrayAccess,
    JsonSerializable,
    Stringable
{
    /**
     * @var array<string, mixed>
     */
    private array $translations;

    /**
     * @param array<string, mixed>|Translation|self $translations Values keyed by locale.
     */
    final public function __construct($translations)
    {
        $this->translations = $this->sanitizeTranslations($translations);
    }

    /**
     * @param array<string, mixed>|Translation|self $value Values keyed by locale.
     * @return array<string, mixed>
     * @throws InvalidArgumentException If the value is invalid.
     */
    protected function sanitizeTranslations($value): array
    {
        if ($value instanceof self) {
            return $value->toArray();
        }

        if ($value instanceof Translation) {
            return $value->data();
        }

        if (\is_array($value)) {
            $translations = [];

            foreach ($value as $locale => $translation) {
                if (!\is_string($locale)) {
                    throw new InvalidArgumentException(\sprintf(
                        'Expected multilingual value locale to be a string, received %s',
                        \gettype($locale)
                    ));
                }

                $translations[$locale] = $translation;
            }

            return $translations;
        }

        throw new InvalidArgumentException(\sprintf(
            'Expected at least one localized value, received %s',
            (\is_object($value) ? \get_class($value) : \gettype($value))
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->translations;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * @param integer $options From {@see \json_encode()} flags.
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return \json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param callable $callback The callback function.
     * @return static
     */
    public function map(callable $callback): self
    {
        $keys = array_keys($this->translations);

        $translations = array_map($callback, $this->translations, $keys);

        return new static(array_combine($keys, $translations));
    }

    /**
     * Transform each translation value.
     *
     * This method is to maintain compatibility with {@see Translation}.
     *
     * @param (callable(mixed, string): mixed) $callback Function to apply to each value.
     * @return self
     *
     * @deprecated Will be removed in future version in favor of keeping this class Immutable.
     */
    public function each(callable $callback): self
    {
        foreach ($this->translations as $locale => $translation) {
            $this->translations[$locale] = \call_user_func($callback, $translation, $locale);
        }
        return $this;
    }

    /**
     * Transform each translation value.
     *
     * This method is to maintain compatibility with {@see Translation}.
     *
     * @param (callable(mixed): mixed) $callback Function to apply to each value.
     * @return self
     *
     * @deprecated Will be removed in future version in favor of keeping this class Immutable.
     */
    public function sanitize(callable $callback): self
    {
        foreach ($this->translations as $locale => $translation) {
            $this->translations[$locale] = \call_user_func($callback, $translation);
        }
        return $this;
    }

    /**
     * @param string $locale The requested locale.
     * @return mixed
     */
    public function toLocale(string $locale)
    {
        return ($this->translations[$locale] ?? null);
    }

    /**
     * @param  TranslatorInterface $translator  The translator.
     * @param  ?string             $locale      The locale.
     * @return mixed
     */
    public function trans(TranslatorInterface $translator, ?string $locale = null)
    {
        $locale ??= $translator->getLocale();

        return $this->toLocale($locale);
    }

    /**
     * @param string $offset The requested offset to test.
     * @return boolean
     * @throws InvalidArgumentException If array key isn't a string.
     * @see    ArrayAccess::offsetExists()
     */
    public function offsetExists($offset): bool
    {
        if (!is_string($offset)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid language; must be a string, received %s',
                (is_object($offset) ? get_class($offset) : gettype($offset))
            ));
        }

        return isset($this->translations[$offset]);
    }

    /**
     * @param string $offset The requested offset.
     * @return mixed
     * @throws InvalidArgumentException If array key isn't a string.
     * @throws DomainException If the array key is not found.
     * @see    ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        if (!is_string($offset)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid language; must be a string, received %s',
                (is_object($offset) ? get_class($offset) : gettype($offset))
            ));
        }

        if (!isset($this->translations[$offset])) {
            throw new DomainException(sprintf(
                'Translation for "%s" is not defined.',
                $offset
            ));
        }

        return $this->translations[$offset];
    }

    /**
     * @param string $offset The lang offset to set.
     * @param mixed  $value  The value to store.
     * @return void
     * @throws InvalidArgumentException If array key isn't a string.
     * @see    ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if (!is_string($offset)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid language; must be a string, received %s',
                (is_object($offset) ? get_class($offset) : gettype($offset))
            ));
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'Translation must be a string, received %s',
                (is_object($value) ? get_class($value) : gettype($value))
            ));
        }

        $this->translations[$offset] = $value;
    }

    /**
     * @param  string $offset The language offset to unset.
     * @return void
     * @throws InvalidArgumentException If array key isn't a string.
     */
    public function offsetUnset($offset)
    {
        if (!is_string($offset)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid language; must be a string, received %s',
                (is_object($offset) ? get_class($offset) : gettype($offset))
            ));
        }

        unset($this->translations[$offset]);
    }
}
