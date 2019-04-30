<?php

namespace Charcoal\Tests\View\Mustache;

use LogicException;

// From Mustache
use Mustache_Engine as MustacheEngine;

// From 'symfony/translation'
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

// From 'charcoal-translator'
use Charcoal\Translator\Translator;
use Charcoal\Translator\LocalesManager;

// From 'charcoal-view'
use Charcoal\View\Mustache\TranslatorHelpers;
use Charcoal\Tests\AbstractTestCase;

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
     * @var MustacheEngine
     */
    private $mustache;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->translator = $this->createTranslator();
        $this->mustache   = $this->createMustacheEngine($this->translator);
    }

    /**
     * @return void
     */
    public function testTransWithoutTranslator()
    {
        $this->mustache = $this->createMustacheEngine();

        $template = $this->mustache->loadTemplate('{{# _t.en }}greet.name{{/ _t.en }}');
        $output   = $template->render([
            'name' => 'World',
        ]);
        $expected = trim('greet.name');

        $this->assertEquals($expected, $output);
    }

    /**
     * @return void
     */
    public function testTransWithUnknownMacro()
    {
        $this->expectException(LogicException::class);

        $this->addTranslatorResources();

        $template = $this->mustache->loadTemplate('{{# _t.num.unknown }}count.apples{{/ _t.num.unknown }}');
        $output   = $template->render([
            'num' => 1,
        ]);
    }

    /**
     * @return void
     */
    public function testTrans()
    {
        // phpcs:disable Squiz.Strings.DoubleQuoteUsage.NotRequired
        $this->addTranslatorResources();

        $template = $this->mustache->loadTemplate(trim("
            {{# _t }}greet.name{{/ _t }}
            {{# _t.fr }}greet.name{{/ _t.fr }}
            {{# _t.slang }}greet.name{{/ _t.slang }}
        "));

        $output   = $template->render([
            'name' => 'World',
        ]);

        $expected = trim("
            Hello World!
            Bonjour World!
            Yo World!
        ");

        $this->assertEquals($expected, $output);
        // phpcs:enable
    }

    /**
     * @return void
     */
    public function testTransChoice()
    {
        // phpcs:disable Squiz.Strings.DoubleQuoteUsage.NotRequired
        $this->addTranslatorResources();

        $template = $this->mustache->loadTemplate(trim("
            {{# _t.5 }}count.apples{{/ _t.5 }}
            {{# _t.fr.num }}count.apples{{/ _t.fr.num }}
        "));

        $output   = $template->render([
            'num' => 1,
        ]);

        $expected = trim("
            There are 5 apples
            Il y a une pomme
        ");

        $this->assertEquals($expected, $output);
        // phpcs:enable
    }

    /**
     * @return void
     */
    public function addTranslatorResources()
    {
        $this->translator->addResource('array', [
            'count.apples' => '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        ], 'en', 'messages');

        $this->translator->addResource('array', [
            'count.apples' => '{0} Il n\'y a pas de pommes|{1} Il y a une pomme|]1,Inf[ Il y a %count% pommes',
        ], 'fr', 'messages');

        $this->translator->addResource('array', [
            'greet.name' => 'Hello {{ name }}!',
        ], 'en', 'messages');

        $this->translator->addResource('array', [
            'greet.name' => 'Bonjour {{ name }}!',
        ], 'fr', 'messages');

        $this->translator->addResource('array', [
            'greet.name' => 'Yo {{ name }}!',
        ], 'en', 'slang');
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
     * @return MustacheEngine
     */
    public function createMustacheEngine($translator = null)
    {
        $helper   = new TranslatorHelpers([
            'translator' => $translator,
        ]);
        $mustache = new MustacheEngine([
            'helpers' => $helper->toArray(),
        ]);

        return $mustache;
    }
}
