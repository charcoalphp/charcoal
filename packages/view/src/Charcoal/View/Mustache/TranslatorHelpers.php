<?php

declare(strict_types=1);

namespace Charcoal\View\Mustache;

use LogicException;
// From Mustache
use Mustache\LambdaHelper;
use Mustache\RenderedString;
// From 'charcoal-translator'
use Charcoal\Translator\Translator;
// From 'charcoal-view'
use Charcoal\View\Mustache\HelpersInterface;

/**
 * Translating Mustache Templates
 */
class TranslatorHelpers implements HelpersInterface
{
    /**
     * Store the translator service.
     *
     * @var Translator|null
     */
    private $translator;

    /**
     * Store the given number to use to find the indice of the message (Mustache tag node).
     *
     * This can be a variable name in the context stack or an integer.
     *
     * Floats are not supported due to their decimal symbol conflicting
     * with Mustache's dot-notation.
     *
     * @var string|integer|null
     */
    private $number;

    /**
     * Store the given locale (Mustache tag node).
     *
     * This must be an available locale on the Translator.
     *
     * @var string|null
     */
    private $locale;

    /**
     * Store the given domain for the message (Mustache tag node).
     *
     * This must be an available domain on the Translator.
     *
     * @var string|null
     */
    private $domain;

    /**
     * @param array $data Class Dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['translator'])) {
            $this->setTranslator($data['translator']);
        }
    }

    /**
     * Set the translator service.
     *
     * @param  Translator $translator The Translator service.
     * @return void
     */
    protected function setTranslator(Translator $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Retrieve the helpers.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            '_t' => $this,
        ];
    }

    /**
     * Clear macros.
     *
     * @return void
     */
    protected function reset(): void
    {
        $this->number = null;
        $this->domain = null;
        $this->locale = null;
    }

    /**
     * Magic: Render the Mustache section.
     *
     * Mustache 3+ may invoke callables while resolving dotted names (e.g. `_t.en`)
     * with zero arguments. In that case, return `$this` so `__get()` can continue.
     *
     * LambdaHelper::render() returns a RenderedString when double-render is disabled;
     * that object must be returned as-is so Mustache does not re-render the output.
     *
     * @param  string|null       $text   The translation key.
     * @param  LambdaHelper|null $helper For rendering strings in the current context.
     * @return string|RenderedString|self
     */
    public function __invoke($text = null, LambdaHelper $helper = null)
    {
        if (func_num_args() === 0) {
            return $this;
        }

        $text = (string)$text;

        if ($this->translator) {
            if ($this->number === null) {
                $text = $this->translator->trans($text, [], $this->domain, $this->locale);
            } else {
                if (!is_numeric($this->number) && is_string($this->number)) {
                    $this->number = (string)$helper->render('{{ ' . $this->number . ' }}');
                }

                $text = $this->translator->transChoice($text, (int)$this->number, [], $this->domain, $this->locale);
            }

            $this->reset();
        }

        if ($helper === null) {
            return $text;
        }

        /** Render any Mustache tags in the translation. */
        return $helper->render($text);
    }

    /**
     * Magic: Determine if a property is set and is not NULL.
     *
     * Required by Mustache.
     *
     * @param  string $macro A domain, locale, or number.
     * @return boolean
     */
    public function __isset(string $macro): bool
    {
        return boolval($macro);
    }

    /**
     * Magic: Process domain, locale, and number.
     *
     * Required by Mustache.
     *
     * @param  string $macro A domain, locale, or number.
     * @throws LogicException If the macro is unresolved.
     * @return mixed
     */
    public function __get(string $macro)
    {
        if (!$this->translator) {
            return $this;
        }

        if ($this->locale === null && in_array($macro, $this->translator->availableLocales())) {
            $this->locale = $macro;
            return $this;
        }

        if ($this->domain === null && in_array($macro, $this->translator->availableDomains())) {
            $this->domain = $macro;
            return $this;
        }

        if ($this->number === null) {
            $this->number = $macro;
            return $this;
        }

        throw new LogicException(sprintf('Unknown translator macro: %s', $macro));
    }
}
