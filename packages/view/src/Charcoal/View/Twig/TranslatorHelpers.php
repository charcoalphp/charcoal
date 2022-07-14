<?php

declare(strict_types=1);

namespace Charcoal\View\Twig;

use Charcoal\Translator\Translator;
use Charcoal\View\Twig\HelpersInterface;
use LogicException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Custom TranslationHelpers for Twig.
 */
class TranslatorHelpers extends AbstractExtension implements
    HelpersInterface
{
    /**
     * Store the translator service.
     *
     * @var Translator|null
     */
    private $translator;

    /**
     * @param array $data Class Dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['translator'])) {
            $this->setTranslator($data['translator']);
        }
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', [ $this, 'trans' ]),
            new TwigFilter('transchoice', [ $this, 'transchoice' ]),
        ];
    }

    /**
     * Translates the given (mixed) message.
     *
     * @uses   SymfonyTranslator::trans()
     * @param  mixed       $message   The string or translation-object to retrieve.
     * @param  array       $arguments An array of parameters for the message.
     * @param  string|null $domain    The domain for the message or NULL to use the default.
     * @param  string|null $locale    The locale or NULL to use the default.
     * @return string The translated string
     */
    public function trans($message, array $arguments = [], $domain = null, $locale = null): string
    {
        if (null === $this->translator) {
            return strtr($message, $arguments);
        }

        return $this->translator->trans($message, $arguments, $domain, $locale);
    }

    /**
     * Translates the given choice message by choosing a translation according to a number.
     *
     * @param string      $message   The message (may also be an object that can be cast to string).
     * @param integer     $count     The number to use to find the index of the message.
     * @param array       $arguments An array of parameters for the message.
     * @param string|null $domain    The domain for the message or null to use the default.
     * @param string|null $locale    The locale or null to use the default.
     *
     * @return string The translated string
     */
    public function transChoice($message, $count, array $arguments = [], $domain = null, $locale = null): ?string
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
    public function toArray(): array
    {
        return [
            static::class => $this,
        ];
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
}
