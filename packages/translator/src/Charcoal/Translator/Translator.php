<?php

namespace Charcoal\Translator;

use AllowDynamicProperties;
use RuntimeException;
// From 'symfony/translation'
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Component\Translation\Translator as SymfonyTranslator;
// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translation;

/**
 * Charcoal Translator.
 *
 * Extends the Symfony translator to allow returned values in a "Translation" oject,
 * containing localizations for all locales.
 */
#[AllowDynamicProperties]
class Translator extends SymfonyTranslator
{
    /**
     * The locales manager.
     */
    private \Charcoal\Translator\LocalesManager $manager;

    /**
     * The message formatter.
     */
    private \Symfony\Component\Translation\Formatter\MessageFormatterInterface $formatter;

    /**
     * The loaded domains.
     *
     * @var string[]
     */
    private array $domains = [ 'messages' ];

    /**
     * @param array $data Translator dependencies.
     */
    public function __construct(array $data)
    {
        $this->setManager($data['manager']);

        // Ensure Charcoal has control of the message formatter.
        if (!isset($data['message_formatter'])) {
            $data['message_formatter'] = new MessageFormatter();
        }
        $this->setFormatter($data['message_formatter']);

        $defaults = [
            'locale'    => $this->manager()->currentLocale(),
            'cache_dir' => null,
            'debug'     => false,
        ];
        $data = array_merge($defaults, $data);

        // If 'symfony/config' is not installed, DON'T use cache.
        if (!class_exists(\Symfony\Component\Config\ConfigCacheFactory::class, false)) {
            $data['cache_dir'] = null;
        }

        parent::__construct(
            $data['locale'],
            $data['message_formatter'],
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
     */
    #[\Override]
    public function addResource(string $format, mixed $resource, string $locale, ?string $domain = null): void
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
    public function availableDomains(): array
    {
        return $this->domains;
    }

    /**
     * Retrieve a translation object from a (mixed) message.
     *
     * @uses   SymfonyTranslator::trans()
     * @param  mixed       $val        The string or translation-object to retrieve.
     * @param  array       $parameters An array of parameters for the message.
     * @param  string|null $domain     The domain for the message or NULL to use the default.
     * @return Translation|null The translation object or NULL if the value is not translatable.
     */
    public function translation($val, array $parameters = [], ?string $domain = null): ?\Charcoal\Translator\Translation
    {
        if ($this->isValidTranslation($val) === false) {
            return null;
        }

        $translation = new Translation($val, $this->manager());
        $localized   = (string)$translation;
        foreach ($this->availableLocales() as $lang) {
            if (!isset($translation[$lang]) || $translation[$lang] === $val) {
                $translation[$lang] = $this->trans($localized, $parameters, $domain, $lang);
            } else {
                $translation[$lang] = strtr(
                    $translation[$lang],
                    $parameters
                );
            }
        }

        return $translation;
    }

    /**
     * Translates the given (mixed) message.
     *
     * @uses   SymfonyTranslator::trans()
     * @uses   Translator::translation()
     * @param  mixed       $val        The string or translation-object to retrieve.
     * @param  array       $parameters An array of parameters for the message.
     * @param  string|null $domain     The domain for the message or NULL to use the default.
     * @param  string|null $locale     The locale or NULL to use the default.
     * @return string The translated string
     */
    public function translate($val, array $parameters = [], ?string $domain = null, $locale = null): string
    {
        if ($locale === null) {
            $locale = $this->getLocale();
        }

        if ($val instanceof Translation) {
            return strtr($val[$locale], $parameters);
        }

        if (is_object($val) && method_exists($val, '__toString')) {
            $val = (string)$val;
        }

        if (is_string($val)) {
            if ($val !== '') {
                return $this->trans($val, $parameters, $domain, $locale);
            }

            return '';
        }

        $translation = $this->translation($val, $parameters, $domain);
        if ($translation instanceof Translation) {
            return $translation[$locale];
        }

        return '';
    }

    /**
     * Retrieve a translation object from a (mixed) message by choosing a translation according to a number.
     *
     * @uses   SymfonyTranslator::transChoice()
     * @param  mixed       $val        The string or translation-object to retrieve.
     * @param  integer     $number     The number to use to find the indice of the message.
     * @param  array       $parameters An array of parameters for the message.
     * @param  string|null $domain     The domain for the message or NULL to use the default.
     * @return Translation|null The translation object or NULL if the value is not translatable.
     */
    public function translationChoice($val, $number, array $parameters = [], ?string $domain = null): ?\Charcoal\Translator\Translation
    {
        if ($this->isValidTranslation($val) === false) {
            return null;
        }

        $parameters = array_merge([
            '%count%' => $number,
        ], $parameters);

        $translation = new Translation($val, $this->manager());
        $localized   = (string)$translation;
        foreach ($this->availableLocales() as $lang) {
            if (!isset($translation[$lang]) || $translation[$lang] === $val) {
                $translation[$lang] = $this->trans($localized, $parameters, $domain, $lang);
            } else {
                $translation[$lang] = $this->formatter()->format($translation[$lang], $lang, $parameters);
            }
        }

        return $translation;
    }

    /**
     * Translates the given (mixed) choice message by choosing a translation according to a number.
     *
     * @uses   SymfonyTranslator::transChoice()
     * @uses   Translator::translationChoice()
     * @param  mixed       $val        The string or translation-object to retrieve.
     * @param  integer     $number     The number to use to find the indice of the message.
     * @param  array       $parameters An array of parameters for the message.
     * @param  string|null $domain     The domain for the message or NULL to use the default.
     * @param  string|null $locale     The locale or NULL to use the default.
     * @return string The translated string
     */
    public function translateChoice($val, $number, array $parameters = [], $domain = null, $locale = null): string
    {
        if ($locale === null) {
            $locale = $this->getLocale();
        }

        if ($val instanceof Translation) {
            $parameters = array_merge([
                '%count%' => $number,
            ], $parameters);

            return $this->formatter()->format($val[$locale], $locale, $parameters);
        }

        if (is_object($val) && method_exists($val, '__toString')) {
            $val = (string)$val;
        }

        if (is_string($val)) {
            if ($val !== '') {
                return $this->trans($val, array_merge(['%count%' => $number], $parameters), $domain, $locale);
            }

            return '';
        }

        $translation = $this->translationChoice($val, $number, $parameters, $domain);
        if ($translation instanceof Translation) {
            return $translation[$locale];
        }

        return '';
    }

    /**
     * Retrieve the available locales information.
     *
     * @return array
     */
    public function locales()
    {
        return $this->manager()->locales();
    }

    /**
     * Retrieve the available locales (language codes).
     *
     * @return string[]
     */
    public function availableLocales(): array
    {
        return $this->manager()->availableLocales();
    }

    /**
     * Sets the current locale.
     *
     * @see    SymfonyTranslator::setLocale() Ensure that the method also changes the locales manager's language.
     * @param  string $locale The locale.
     */
    #[\Override]
    public function setLocale(string $locale): void
    {
        parent::setLocale($locale);

        $this->manager()->setCurrentLocale($locale);
    }

    /**
     * Set the locales manager.
     *
     * @param  LocalesManager $manager The locales manager.
     */
    private function setManager(LocalesManager $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * Retrieve the locales manager.
     */
    protected function manager(): \Charcoal\Translator\LocalesManager
    {
        return $this->manager;
    }

    /**
     * Set the message formatter.
     *
     * The {@see SymfonyTranslator} keeps the message formatter private (as of 3.3.2),
     * thus we must explicitly require it in this class to guarantee access.
     *
     * @param  MessageFormatterInterface $formatter The formatter.
     */
    public function setFormatter(MessageFormatterInterface $formatter): void
    {
        $this->formatter = $formatter;
    }

    /**
     * Retrieve the message formatter.
     */
    protected function formatter(): \Symfony\Component\Translation\Formatter\MessageFormatterInterface
    {
        return $this->formatter;
    }

    /**
     * Checks if a message has a translation.
     *
     * @param  string      $id     The message id.
     * @param  string|null $domain The domain for the message or NULL to use the default.
     * @param  string|null $locale The locale or NULL to use the default.
     * @return boolean TRUE if the message has a translation, FALSE otherwise.
     */
    public function hasTrans(string $id, $domain = null, ?string $locale = null): bool
    {
        if (null === $domain) {
            $domain = 'messages';
        }

        return $this->getCatalogue($locale)->has($id, $domain);
    }

    /**
     * Checks if a message has a translation (it does not take into account the fallback mechanism).
     *
     * @param  string      $id     The message id.
     * @param  string|null $domain The domain for the message or NULL to use the default.
     * @param  string|null $locale The locale or NULL to use the default.
     * @return boolean TRUE if the message has a translation, FALSE otherwise.
     */
    public function transExists(string $id, $domain = null, ?string $locale = null): bool
    {
        if (null === $domain) {
            $domain = 'messages';
        }

        return $this->getCatalogue($locale)->defines($id, $domain);
    }

    /**
     * Determine if the value is translatable.
     *
     * @param  mixed $val The value to be checked.
     * @return boolean
     */
    protected function isValidTranslation($val)
    {
        if (empty($val) && !is_numeric($val)) {
            return false;
        }

        if (is_string($val)) {
            return !in_array(trim($val), ['', '0'], true);
        }

        if ($val instanceof Translation) {
            return true;
        }

        if (is_array($val)) {
            return (bool)array_filter(
                $val,
                fn($v, $k): bool => is_string($k) && $k !== '' && (is_string($v) && $v !== ''),
                ARRAY_FILTER_USE_BOTH
            );
        }
        return false;
    }
}
