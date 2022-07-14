<?php

namespace Charcoal\Translator;

use RuntimeException;
// From 'symfony/translation'
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Component\Translation\MessageSelector;
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
class Translator extends SymfonyTranslator
{
    /**
     * The locales manager.
     *
     * @var LocalesManager
     */
    private $manager;

    /**
     * The message selector.
     *
     * @var MessageSelector
     */
    private $selector;

    /**
     * The message formatter.
     *
     * @var MessageFormatterInterface
     */
    private $formatter;

    /**
     * The loaded domains.
     *
     * @var string[]
     */
    private $domains = [ 'messages' ];

    /**
     * @param array $data Translator dependencies.
     */
    public function __construct(array $data)
    {
        $this->setManager($data['manager']);

        // Ensure Charcoal has control of the message selector.
        if (!isset($data['message_selector'])) {
            $data['message_selector'] = new MessageSelector();
        }
        $this->setSelector($data['message_selector']);

        // Ensure Charcoal has control of the message formatter.
        if (!isset($data['message_formatter'])) {
            $data['message_formatter'] = new MessageFormatter($data['message_selector']);
        }
        $this->setFormatter($data['message_formatter']);

        $defaults = [
            'locale'    => $this->manager()->currentLocale(),
            'cache_dir' => null,
            'debug'     => false,
        ];
        $data = array_merge($defaults, $data);

        // If 'symfony/config' is not installed, DON'T use cache.
        if (!class_exists('\Symfony\Component\Config\ConfigCacheFactory', false)) {
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
     * Retrieve a translation object from a (mixed) message.
     *
     * @uses   SymfonyTranslator::trans()
     * @param  mixed       $val        The string or translation-object to retrieve.
     * @param  array       $parameters An array of parameters for the message.
     * @param  string|null $domain     The domain for the message or NULL to use the default.
     * @return Translation|null The translation object or NULL if the value is not translatable.
     */
    public function translation($val, array $parameters = [], $domain = null)
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
    public function translate($val, array $parameters = [], $domain = null, $locale = null)
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
    public function translationChoice($val, $number, array $parameters = [], $domain = null)
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
                $translation[$lang] = $this->transChoice($localized, $number, $parameters, $domain, $lang);
            } else {
                $translation[$lang] = strtr(
                    $this->selector()->choose($translation[$lang], (int)$number, $lang),
                    $parameters
                );
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
    public function translateChoice($val, $number, array $parameters = [], $domain = null, $locale = null)
    {
        if ($locale === null) {
            $locale = $this->getLocale();
        }

        if ($val instanceof Translation) {
            $parameters = array_merge([
                '%count%' => $number,
            ], $parameters);

            return strtr(
                $this->selector()->choose($val[$locale], (int)$number, $locale),
                $parameters
            );
        }

        if (is_object($val) && method_exists($val, '__toString')) {
            $val = (string)$val;
        }

        if (is_string($val)) {
            if ($val !== '') {
                return $this->transChoice($val, $number, $parameters, $domain, $locale);
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
    public function availableLocales()
    {
        return $this->manager()->availableLocales();
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

        $this->manager()->setCurrentLocale($locale);
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
     * Retrieve the locales manager.
     *
     * @return LocalesManager
     */
    protected function manager()
    {
        return $this->manager;
    }

    /**
     * Set the message selector.
     *
     * The {@see SymfonyTranslator} keeps the message selector private (as of 3.3.2),
     * thus we must explicitly require it in this class to guarantee access.
     *
     * @param  MessageSelector $selector The selector.
     * @return void
     */
    public function setSelector(MessageSelector $selector)
    {
        $this->selector = $selector;
    }

    /**
     * Retrieve the message selector.
     *
     * @return MessageSelector
     */
    protected function selector()
    {
        return $this->selector;
    }

    /**
     * Set the message formatter.
     *
     * The {@see SymfonyTranslator} keeps the message formatter private (as of 3.3.2),
     * thus we must explicitly require it in this class to guarantee access.
     *
     * @param  MessageFormatterInterface $formatter The formatter.
     * @return void
     */
    public function setFormatter(MessageFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Retrieve the message formatter.
     *
     * @return MessageFormatterInterface
     */
    protected function formatter()
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
    public function hasTrans($id, $domain = null, $locale = null)
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
    public function transExists($id, $domain = null, $locale = null)
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
            return !empty(trim($val));
        }

        if ($val instanceof Translation) {
            return true;
        }

        if (is_array($val)) {
            return !!array_filter(
                $val,
                function ($v, $k) {
                    if (is_string($k) && strlen($k) > 0) {
                        if (is_string($v) && strlen($v) > 0) {
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
