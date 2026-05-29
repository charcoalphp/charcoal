<?php

namespace Charcoal\Tests\Translation\ServiceProvider;

// From Pimple
use Charcoal\App\AppConfig;
use Pimple\Container;

// From 'charcoal-translator'
use Charcoal\Translator\Middleware\LanguageMiddleware;
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translator;
use Charcoal\Tests\Translator\AbstractTestCase;

/**
 *
 */
class TranslatorServiceProviderTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\Translator\ServiceProvider\TranslatorServiceProvider $obj;

    /**
     * Service Container.
     */
    private \Pimple\Container $container;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $this->obj = new TranslatorServiceProvider();
        $this->container = new Container();
        $this->container['config'] = new AppConfig([
            'base_path' => realpath(__DIR__.'/../../..'),
            'locales'   => [
                'languages' => [
                    'en' => [ 'locale' => 'en-US' ],
                    'fr' => [ 'locale' => 'fr-FR' ],
                ],
                'default_language'   => 'en',
                'fallback_languages' => [ 'en' ],
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
                    'yaml',
                ],
                'paths' => [
                    '/Charcoal/Translator/Fixture/translations',
                    '/Charcoal/Translator/Fixture/nonexistent',
                ],
                'translations' => [
                    'messages' => [
                        'en' => [
                            'foo' => 'FOO'
                        ],
                        'fr' => [
                            'foo' => 'OOF'
                        ]
                    ]
                ],
                'debug' => false,
                'cache_dir' => 'translator_cache',
            ],
            'middlewares' => [
                'charcoal/translator/middleware/language' => []
            ]
        ]);

        $this->container->register($this->obj);
    }

    /**
     * @return void
     */
    protected function resetDefaultLanguage()
    {
        static $raw;

        if ($raw === null) {
            $raw = $this->container->raw('locales/default-language');
        }

        unset($this->container['locales/default-language']);
        $this->container['locales/default-language'] = $raw;
    }

    public function testKeys(): void
    {
        $this->assertFalse(isset($this->container['foofoobarbarbaz']));
        $this->assertTrue(isset($this->container['locales/config']));
        $this->assertTrue(isset($this->container['locales/available-languages']));
        $this->assertTrue(isset($this->container['locales/default-language']));
        $this->assertTrue(isset($this->container['locales/browser-language']));
        $this->assertTrue(isset($this->container['translator']));
        $this->assertTrue(isset($this->container['middlewares/charcoal/translator/middleware/language']));
    }

    public function testAvailableLanguages(): void
    {
        $languages = $this->container['locales/available-languages'];
        $this->assertContains('en', $languages);
    }

    public function testLanguages(): void
    {
        $languages = $this->container['locales/languages'];
        $this->assertArrayHasKey('en', $languages);
    }

    public function testDefaultLanguage(): void
    {
        $defaultLanguage = $this->container['locales/default-language'];
        $this->assertEquals('en', $defaultLanguage);
    }

    public function testBrowserLanguageIsNullWithoutHttp(): void
    {
        $browserLanguage = $this->container['locales/browser-language'];
        $this->assertNull($browserLanguage);
    }

    public function testBrowserLanguage(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr';
        $browserLanguage = $this->container['locales/browser-language'];
        $this->assertEquals('fr', $browserLanguage);
    }

    public function testBrowserLanguageIsNullIfInvalidHttp(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'baz';
        $browserLanguage = $this->container['locales/browser-language'];
        $this->assertNull($browserLanguage);
    }

    public function testDetectedLanguageIsNullWithoutHttp(): void
    {
        $this->container['locales/config']->setAutoDetect(true);

        $this->resetDefaultLanguage();

        $defaultLanguage = $this->container['locales/default-language'];
        $this->assertEquals('en', $defaultLanguage);

        $this->container['locales/config']->setAutoDetect(false);
    }

    public function testDetectedLanguage(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr';
        $this->container['locales/config']->setAutoDetect(true);

        $this->resetDefaultLanguage();

        $defaultLanguage = $this->container['locales/default-language'];
        $this->assertEquals('fr', $defaultLanguage);

        $this->container['locales/config']->setAutoDetect(false);
    }

    public function testFallbackLanguages(): void
    {
        $fallbackLanguages = $this->container['locales/fallback-languages'];
        $this->assertEquals([ 'en' ], $fallbackLanguages);
    }

    public function testLanguageManager(): void
    {
        $manager = $this->container['locales/manager'];
        $this->assertInstanceOf(LocalesManager::class, $manager);
    }

    public function testTranslator(): void
    {
        $translator = $this->container['translator'];
        $this->assertInstanceOf(Translator::class, $translator);
    }

    public function testMiddleware(): void
    {
        $middleware = $this->container['middlewares/charcoal/translator/middleware/language'];
        $this->assertInstanceOf(LanguageMiddleware::class, $middleware);
    }
}
