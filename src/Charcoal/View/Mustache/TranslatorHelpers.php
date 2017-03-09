<?php

namespace Charcoal\View\Mustache;

use Closure;
use InvalidArgumentException;

// From 'mustache/mustache'
use Mustache_LambdaHelper as LambdaHelper;

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
     * Store the given number to use to find the indice of the message.
     *
     * Requires {@link https://github.com/bobthecow/mustache.php/wiki/FILTERS-pragma FILTERS pragma}.
     *
     * @var integer|null
     */
    private $number;

    /**
     * Store the given locale (Mustache tag node).
     *
     * @var string|null
     */
    private $locale;

    /**
     * Store the given domain for the message (Mustache tag node).
     *
     * @var string|null
     */
    private $domain;

    /**
     * @param array $data Class Dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['translator']) && $data['translator'] instanceof Translator) {
            $this->translator = $data['translator'];
        }
    }

    /**
     * Retrieve the helpers.
     *
     * @todo   Implement plural translations.
     * @return array
     */
    public function toArray()
    {
        return [
            '_t' => $this
        ];
    }

    /**
     * Clear macros.
     *
     * @return void
     */
    protected function reset()
    {
        $this->number = null;
        $this->domain = null;
        $this->locale = null;
    }



    // Magic Methods
    // =========================================================================

    /**
     * Magic: Render the Mustache section.
     *
     * @param  string            $text   The translation key.
     * @param  LambdaHelper|null $helper For rendering strings in the current context.
     * @return boolean
     */
    public function __invoke($text, LambdaHelper $helper = null)
    {
        if (!$helper) {
            $this->number = $text;
            return $this;
        }

        if ($this->translator) {
            if ($this->number === null) {
                $text = $this->translator->trans($text, [], $this->domain, $this->locale);
            } else {
                if (!is_numeric($this->number) && is_string($this->number)) {
                    $this->number = $helper->render('{{ '.$this->number.' }}');
                }

                $text = $this->translator->transChoice($text, (int)$this->number, [], $this->domain, $this->locale);
            }

            $this->reset();
        }

        /** @var string Render any Mustache tags in the translation. */
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
    public function __isset($macro)
    {
        return boolval($macro);
    }

    /**
     * Magic: Process domain, locale, and number.
     *
     * Required by Mustache.
     *
     * @param  string $macro A domain, locale, or number.
     * @return mixed
     */
    public function __get($macro)
    {
        if (!$this->translator) {
            return $this;
        }

        if ($macro === '_t' || $macro === '_n') {
            return $this;
        }

        if (in_array($macro, $this->translator->availableLocales())) {
            $this->locale = $macro;
        } elseif (in_array($macro, $this->translator->availableDomains())) {
            $this->domain = $macro;
        } else {
            $this->number = $macro;
        }

        return $this;
    }
}
