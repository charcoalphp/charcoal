<?php

namespace Charcoal\View\Twig;

use LogicException;

// From 'charcoal-translator'
use Charcoal\Translator\Translator;

// From 'charcoal-view'
use Charcoal\View\Twig\HelpersInterface;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranslatorHelpers extends AbstractExtension
    implements HelpersInterface
{
    /**
     * Store the translator service.
     *
     * @var Translator|null
     */
    private $translator;

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

    public function getFilters()
    {
        return [
            new TwigFilter('trans', [ $this, 'trans' ]),
            new TwigFilter('transchoice', [ $this, 'transchoice' ]),
        ];
    }

    /**
     * Render the Twig section.
     *
     * @param  string            $message   The translation key.
     * @return string
     */
    public function trans($message, array $arguments = [], $domain = null, $locale = null)
    {
        if (null === $this->translator) {
            return strtr($message, $arguments);
        }

        return $this->translator->trans($message, $arguments, $domain, $locale);
    }

    public function transchoice($message, $count, array $arguments = [], $domain = null, $locale = null)
    {
        if (null === $this->translator) {
            return strtr($message, $arguments);
        }

        return $this->translator->transChoice($message, $count, array_merge(['%count%' => $count], $arguments), $domain, $locale);
    }

    /**
     * Retrieve the helpers.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'Charcoal\View\Twig\TranslatorHelpers' => $this,
        ];
    }

    /**
     * Set the translator service.
     *
     * @param  Translator $translator The Translator service.
     * @return void
     */
    protected function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }
}
