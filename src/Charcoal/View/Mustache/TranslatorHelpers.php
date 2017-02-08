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
            '_t' => function ($text, LambdaHelper $helper) {
                if ($this->translator) {
                    $text = $this->translator->trans($text);
                }

                /** @var string Render any Mustache tags in the translation. */
                return $helper->render($text);
            }
        ];
    }
}
