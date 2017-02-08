<?php

namespace Charcoal\Tests\Translation\ServiceProvider;

use PHPUnit_Framework_TestCase;

use Pimple\Container;

// Local Dependencies
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translator;

/**
 *
 */
class TranslatorServiceProviderTest extends PHPUnit_Framework_TestCase
{
    private $obj;
    private $container;

    public function setUp()
    {
        $this->obj = new TranslatorServiceProvider();
        $this->container = new Container();
        $this->container['config'] = [
            'base_path' => realpath(__DIR__.'/../../..'),
            'locales'   => [
                'languages' => [
                    'foo' => [ 'locale' => 'foo-FOO' ],
                    'bar' => [ 'locale' => 'bar-BAR' ]
                ],
                'default_language'   => 'foo',
                'fallback_languages' => [ 'foo' ]
            ],
            'translator' => [
                'loaders' => [
                    'csv',
                    'dat',
                    'res',
                    'ini',
                    'json',
                    'mo',
                    'php',
                    'po',
                    'qt',
                    'xliff',
                    'yaml'
                ],
                'paths' => [
                    'translations/'
                ],
                'debug' => false,
                'cache_dir' => 'translator_cache'
            ]
        ];

        $this->container->register($this->obj);
    }

    public function testKeys()
    {
        $this->assertFalse(isset($this->container['foofoobarbarbaz']));
        $this->assertTrue(isset($this->container['locales/config']));
        $this->assertTrue(isset($this->container['locales/available-languages']));
        $this->assertTrue(isset($this->container['locales/default-language']));
        $this->assertTrue(isset($this->container['locales/browser-language']));
        $this->assertTrue(isset($this->container['translator/message-selector']));
        $this->assertTrue(isset($this->container['translator']));
    }

    public function testAvailableLanguages()
    {
        $languages = $this->container['locales/available-languages'];
        $this->assertContains('foo', $languages);
    }

    public function testLanguages()
    {
        $languages = $this->container['locales/languages'];
        $this->assertArrayHasKey('foo', $languages);
    }

    public function testDefaultLanguage()
    {
        $defaultLanguage = $this->container['locales/default-language'];
        $this->assertEquals('foo', $defaultLanguage);
    }

    public function testBrowserLanguageIsNullWithoutHttp()
    {
        $browserLanguage = $this->container['locales/browser-language'];
        $this->assertNull($browserLanguage);
    }

    public function testBrowserLanguage()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'bar';
        $browserLanguage = $this->container['locales/browser-language'];
        $this->assertEquals('bar', $browserLanguage);
    }

    public function testBrowserLanguageIsNullIfInvalidHttp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'baz';
        $browserLanguage = $this->container['locales/browser-language'];
        $this->assertNull($browserLanguage);
    }

    public function testFallbackLanguages()
    {
        $fallbackLanguages = $this->container['locales/fallback-languages'];
        $this->assertEquals(['foo'], $fallbackLanguages);
    }

    public function testLanguageManager()
    {
        $manager = $this->container['locales/manager'];
        $this->assertInstanceOf(LocalesManager::class, $manager);
    }

    public function testTranslator()
    {
        $translator = $this->container['translator'];
        $this->assertInstanceOf(Translator::class, $translator);
    }
}
