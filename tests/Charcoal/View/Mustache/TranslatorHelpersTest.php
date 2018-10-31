<?php

namespace Charcoal\Tests\View\Mustache;

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
     * @var TranslatorHelpers
     */
    private $obj;

    /**
     * @var MustacheEngine
     */
    private $mustache;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->translator = new Translator([
            'locale'            => 'en',
            'cache_dir'         => null,
            'debug'             => false,
            'manager'           => new LocalesManager([
                'locales' => [
                    'en' => [
                        'locale' => 'en_US.UTF8'
                    ],
                    'fr' => [
                        'locale' => 'fr_FR.UTF8'
                    ]
                ],
                'default_language'   => 'en',
                'fallback_languages' => [ 'en' ]

            ]),
            'message_selector'  => new MessageSelector()
        ]);

        $this->obj = new TranslatorHelpers([
            'translator' => $this->translator
        ]);
        $this->mustache = new MustacheEngine([
            'helpers' => $this->obj->toArray()
        ]);
    }

    /**
     * @return void
     */
    public function testTranslator()
    {
        $loader = new ArrayLoader();
        $this->translator->addLoader('array', $loader);
        $this->translator->addResource('array', ['string1'=>'Hello', 'string2'=>'World!'], 'en', 'messages');

        $template = $this->mustache->loadTemplate(
            '{{# _t }}string1{{/ _t }} {{# _t }}string2{{/ _t }}'
        );

        $ret = $template->render();
        $this->assertEquals('Hello World!', $ret);
    }
}
