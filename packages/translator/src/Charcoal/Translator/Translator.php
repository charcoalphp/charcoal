<?php

namespace Charcoal\Translator;

use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
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

        // Ensure Charcoal has control of the message formatter.
        if (!isset($data['message_formatter'])) {
            $data['message_formatter'] = new MessageFormatter(($data['message_selector'] ?? null));
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
     * Get unparsed translation object
     * @param Translation|array|string $val
     */
    public function translationRaw($val, ?string $domain = "messages"): ?Translation
    {
        if ($this->isValidTranslation($val) === false) {
            return null;
        }

        $translation = new Translation($val, $this->manager());
        $localized   = (string)$translation;
        foreach ($this->availableLocales() as $lang) {
            if (!isset($translation[$lang]) || $translation[$lang] === $val) {
                $translation[$lang] = $this->trans($localized, [], $domain, $lang);
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

        // Convert old parameters
        $oldParameters = $parameters;
        $parameters = [];
        foreach ($oldParameters as $key => $value) {
            $key = preg_replace('/^%(\w+)%$/', '$1', $key);
            $parameters[$key] = $value;
        }

        $parameters = array_merge([
            'count' => $number,
        ], $parameters);

        $translation = new Translation($val, $this->manager());
        $localized   = (string)$translation;

        foreach ($this->availableLocales() as $lang) {
            $hasTranslation = $this->hasTrans($localized, ($domain ?? null), $lang);
            if (!isset($translation[$lang]) || $translation[$lang] === $val) {
                if ($hasTranslation) {
                    $translation[$lang] = $this->translationRaw($localized, ($domain ?? null))[$lang];
                    $translation[$lang] = $this->convertLegacyChoiceFormat((string)$translation[$lang], $parameters);
                } elseif (isset($translation[$lang])) {
                    $translation[$lang] = $this->convertLegacyChoiceFormat((string)$val, $parameters);
                } else {
                    continue;
                }
            } else {
                $translation[$lang] = $this->convertLegacyChoiceFormat((string)$translation[$lang], $parameters);
            }

            $translation[$lang] = $this->formatMessage($lang, $translation[$lang], $parameters);
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

        // Convert old parameters
        $oldParameters = $parameters;
        $parameters = [];
        foreach ($oldParameters as $key => $value) {
            if ($key != '%count%') {
                $key = preg_replace('/^%(\w+)%$/', '$1', $key);
                $parameters[$key] = $value;
            } else {
                $parameters[$key] = $value;
            }
        }

        $parameters = array_merge([
            'count' => $number,
        ], $parameters);

        if ($val instanceof Translation) {
            // Convert any legacy patterns inside the Translation for all locales
            $val->sanitize(function ($string) use ($parameters) {
                return $this->convertLegacyChoiceFormat((string)$string, $parameters);
            });

            // Prefer the locale-specific pattern if present
            $pattern = ($val[$locale] ?? (string)$val);

            return $this->formatMessage($locale, (string)$pattern, $parameters);
        }

        if (is_object($val) && method_exists($val, '__toString')) {
            $val = (string)$val;
        }

        if (is_string($val)) {
            if ($val !== '') {
                if ($this->hasTrans($val, ($domain ?? null), $locale)) {
                    $translation = $this->translationRaw($val, ($domain ?? null));
                    $val = $translation[$locale];
                }

                $val = $this->convertLegacyChoiceFormat((string)$val, $parameters);
                return $this->formatMessage($locale, $val, $parameters);
            }

            return '';
        }

        $translation = $this->translationChoice($val, $number, $parameters, $domain);
        if ($translation instanceof Translation) {
            return $translation[$locale];
        }

        return '';
    }

    private function formatMessage(string $locale, string $pattern, array $parameters)
    {
        $originalParams = $parameters;

        // Normalize keys: accept "{count}", "%count%" or "count"
        $normalized = [];
        foreach ($parameters as $k => $v) {
            if (is_string($k) && preg_match('/^\{(.+)\}$/', $k, $m)) {
                $normalized[$m[1]] = $v;
            } elseif (is_string($k) && preg_match('/^%(.+)%$/', $k, $m)) {
                if ($m[0] !== '%count%') {
                    $normalized[$m[1]] = $v;
                } else {
                    $pattern = str_replace($m[0], $v, $pattern);
                }
            } else {
                $normalized[$k] = $v;
            }
        }

        // Coerce numeric 'count' to int/float when possible (count may be a numeric string)
        /*if (isset($normalized['count'])) {
            if (is_numeric($normalized['count'])) {
                $normalized['count'] = (strpos((string)$normalized['count'], '.') === false)
                    ? (int)$normalized['count']
                    : (float)$normalized['count'];
            }
        }*/

        // Try intl MessageFormatter (named args). If it fails for named args and we have a numeric count,
        // try positional fallback: change {count, ...} => {0, ...} and pass [count]
        try {
            if (class_exists('\MessageFormatter')) {
                $mf = \MessageFormatter::create($locale, $pattern);
                if ($mf !== null) {
                    $res = $mf->format($normalized);
                    if ($res === false && isset($normalized['count'])) {
                        // positional fallback for 'count' (some ICU builds expect positional args)
                        $posPattern = preg_replace('/\{\s*count\b/i', '{0', $pattern);
                        $posPattern = str_replace('{count}', '{0}', $posPattern);
                        $mf2 = \MessageFormatter::create($locale, $posPattern);
                        if ($mf2 !== null) {
                            $args = [ $normalized['count'] ];
                            $res2 = $mf2->format($args);
                            if ($res2 !== false) {
                                $res = $res2;
                            }
                        }
                    }

                    if ($res !== false) {
                        // Post-format: apply legacy %key% or {key} overrides (keep these as last step)
                        foreach ($originalParams as $ok => $ov) {
                            if (!is_string($ok)) {
                                continue;
                            }
                            if (preg_match('/^\{(.+)\}$/', $ok, $m)) {
                                $res = str_replace('{' . $m[1] . '}', (string)$ov, $res);
                            } elseif (preg_match('/^%(.+)%$/', $ok, $m)) {
                                $res = str_replace('%' . $m[1] . '%', (string)$ov, $res);
                            } else {
                                $res = str_replace('{' . $ok . '}', (string)$ov, $res);
                                $res = str_replace('%' . $ok . '%', (string)$ov, $res);
                            }
                        }

                        return $res;
                    }
                }
            }
        } catch (\Throwable $e) {
            // fallthrough to manual fallback below
        }

        // Manual fallback: replace placeholders but avoid touching plural/select selectors.
        $out = $pattern;
        foreach ($normalized as $k => $v) {
            $placeholder = preg_quote($k, '/');
            $out = preg_replace_callback(
                ['/\\{' . $placeholder . '\\}(?!\\s*,\\s*(plural|select|selectordinal))/i', '/%' . $placeholder . '%/'],
                function ($m) use ($v) {
                    return (string)$v;
                },
                $out
            );
        }

        // Apply explicit legacy overrides after manual formatting
        foreach ($originalParams as $ok => $ov) {
            if (!is_string($ok)) {
                continue;
            }
            if (preg_match('/^\{(.+)\}$/', $ok, $m)) {
                $out = str_replace('{' . $m[1] . '}', (string)$ov, $out);
            } elseif (preg_match('/^%(.+)%$/', $ok, $m)) {
                $out = str_replace('%' . $m[1] . '%', (string)$ov, $out);
            } else {
                $out = str_replace('{' . $ok . '}', (string)$ov, $out);
                $out = str_replace('%' . $ok . '%', (string)$ov, $out);
            }
        }

        return $out;
    }

    private function convertLegacyChoiceFormat(string $string, ?array $parameters = []): string
    {
        // If already looks like ICU plural/select, return
        if (preg_match('/\{\s*count\s*,\s*(plural|select|selectordinal)\b/i', $string)) {
            return $string;
        }

        // If there is no '|' return as-is (nothing to convert)
        if (strpos($string, '|') === false) {
            return $string;
        }

        // IMPORTANT: do NOT convert %count% -> {count}. Use '#' inside branch text for ICU numeric placeholder.
        $parts = explode('|', $string);
        $rules = [];

        foreach ($parts as $part) {
            $part = trim($part);

            // exact number: "{0} text"
            if (preg_match('/^\{(-?\d+)\}\s*(.*)$/u', $part, $m)) {
                $num  = (int)$m[1];
                $text = str_replace('{count}', '#', $m[2]);
                $text = str_replace('%count%', '%count%', $text); // keep %count% for post-replace
                $rules["=" . $num] = $text;
                continue;
            }

            // Interval: "[0,1] text" or "]1,Inf] text" etc.
            if (preg_match('/^([\[\]])\s*([^\],]+)\s*,\s*([^\]\s]+)\s*([\[\]])\s*(.*)$/u', $part, $m)) {
                $lowRaw  = $m[2];
                $highRaw = $m[3];
                $text    = str_replace('{count}', '#', $m[5]);
                $text    = str_replace('%count%', '%count%', $text);
                $low  = ($lowRaw === '-Inf') ? null : (is_numeric($lowRaw) ? (int)$lowRaw : null);
                $high = ($highRaw === 'Inf') ? null : (is_numeric($highRaw) ? (int)$highRaw : null);

                if ($low !== null && $high !== null && ($high - $low) <= 50) {
                    for ($n = $low; $n <= $high; $n++) {
                        $rules["=" . $n] = $text;
                    }
                } else {
                    if ($low === 0 && $high === 1) {
                        $rules['=0'] = $text;
                        $rules['=1'] = $text;
                    } elseif ($low !== null && $high === null && $low >= 1) {
                        $rules['other'] = $text;
                    } else {
                        // fallback: push to 'other'
                        $rules['other'] = $text;
                    }
                }
                continue;
            }

            // Named key: "one: text" or "more: text"
            if (preg_match('/^([a-zA-Z_]+)\s*:\s*(.*)$/u', $part, $m)) {
                $name = strtolower($m[1]);
                $text = str_replace('{count}', '#', $m[2]);
                $text = str_replace('%count%', '%count%', $text);
                if ($name === 'more' || $name === 'other') {
                    $rules['other'] = $text;
                } elseif ($name === 'one') {
                    $rules['one'] = $text;
                } else {
                    // unknown name -> keep as other
                    $rules[$name] = $text;
                }
                continue;
            }

            // plain fallback: if two parts, assume singular|plural -> one/other
            if (count($parts) === 2) {
                $rules['one'] = str_replace('{count}', '#', $parts[0]);
                $rules['other'] = str_replace('{count}', '#', $parts[1]);
                break;
            }

            $rules['other'] = str_replace('{count}', '#', $part);
        }

        // If no rules determined, return original
        if (empty($rules)) {
            return $string;
        }

        // Build ICU plural pattern with '#' inside branches and keep %count% tokens for later replacement.
        $pieces = [];
        // preserve order: =n, one, other etc.
        // sort keys so exact matches come first
        uksort($rules, function ($a, $b) {
            $pa = ($a[0] === '=') ? 0 : 1;
            $pb = ($b[0] === '=') ? 0 : 1;
            if ($pa !== $pb) {
                return ($pa - $pb);
            }
            if ($a === 'one') {
                return -1;
            }
            if ($b === 'one') {
                return 1;
            }
            if ($a === 'other') {
                return 1;
            }
            if ($b === 'other') {
                return -1;
            }
            return strcmp($a, $b);
        });

        foreach ($rules as $selector => $text) {
            $pieces[] = $selector . ' {' . trim($text) . '}';
        }

        $result = '{count, plural, ' . implode(' ', $pieces) . '}';

        return $result;
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
    public function setLocale(string $locale): void
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
