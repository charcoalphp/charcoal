<?php

namespace Charcoal\Tests\View\Twig;

use LogicException;

// From Twig
use Twig\Environment as TwigEnvironment;

// From 'symfony/translation'
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

// From 'charcoal-translator'
use Charcoal\Translator\Translator;
use Charcoal\Translator\LocalesManager;

// From 'charcoal-view'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\View\Twig\TranslatorHelpers;
use Charcoal\View\Twig\TwigLoader;

/**
 *
 */
class TranslatorHelpersTest extends AbstractTestCase
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var TwigEngine
     */
    private $twig;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->translator = $this->createTranslator();
        $this->twig   = $this->createTwigEngine($this->translator);
    }

    /**
     * @return Translator
     */
    public function createTranslator()
    {
        $translator = new Translator([
            'locale'            => 'en',
            'cache_dir'         => null,
            'debug'             => false,
            'message_selector'  => new MessageSelector(),
            'manager'           => new LocalesManager([
                'locales' => [
                    'en' => [
                        'locale' => 'en_US.UTF8',
                    ],
                    'fr' => [
                        'locale' => 'fr_FR.UTF8',
                    ]
                ],
                'default_language'   => 'en',
                'fallback_languages' => [ 'en' ],

            ]),
        ]);

        // phpcs:disable Squiz.Objects.ObjectInstantiation.NotAssigned
        $translator->addLoader('array', new ArrayLoader());
        // phpcs:enable

        return $translator;
    }

    /**
     * @param  Translator|null $translator The translator service for the translator helpers.
     * @return TwigEnvironment
     */
    public function createTwigEngine($translator = null)
    {
        $loader = new TwigLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
        $twig = new TwigEnvironment($loader, [
            'cache'             => false,
            'charset'           => 'utf-8',
            'auto_reload'       => false,
            'strict_variables'  => true,
            'debug'             => true,
        ]);

        $helper   = new TranslatorHelpers([
            'translator' => $translator,
        ]);
        $twig->setExtensions($helper->toArray());

        return $twig;
    }
}
