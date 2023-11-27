<?php

namespace Charcoal\Translator;

use InvalidArgumentException;
use JsonSerializable;
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
    JsonSerializable,
    \Stringable
{
    /**
     * @var array<string, mixed>
     */
    private array $translations;

    /** @var array  */
    private array $parameters;

    /** @var string|null */
    private ?string $domain;

    /**
     * @param array<string, mixed>|Translation|self $translations Values keyed by locale.
     * @param array                                 $parameters   An array of parameters for the message.
     * @param string|null                           $domain       The domain for the message or NULL to use the default.
     */
    public function __construct($translations, array $parameters = [], string $domain = null)
    {
        $this->translations = $this->sanitizeTranslations($translations);
        $this->parameters = $parameters;
        $this->domain = $domain;
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
     * Transform each translation value.
     *
     * This method is to maintain compatibility with {@see Translation}.
     *
     * @param (callable(mixed, string): mixed) $callback Function to apply to each value.
     * @return self
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
     */
    public function sanitize(callable $callback): self
    {
        foreach ($this->translations as $locale => $translation) {
            $this->translations[$locale] = \call_user_func($callback, $translation);
        }
        return $this;
    }

    /**
     * @param Translator  $translator The translator.
     * @param string|null $locale     The locale.
     * @return string
     */
    public function trans(Translator $translator, ?string $locale = null): string
    {
        return $translator->translate($this->translations, $this->parameters, $this->domain, $locale);
    }
}
