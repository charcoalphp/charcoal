<?php

namespace Charcoal\Tests\Translation\ServiceProvider;

use Charcoal\App\AppConfig;
use DI\Container;
use Charcoal\Translator\Middleware\LanguageMiddleware;
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translator;
use Charcoal\Tests\Translator\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TranslatorServiceProvider::class)]
class TranslatorServiceProviderTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var TranslatorServiceProvider
     */
    private $obj;

    /**
     * Service Container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = new TranslatorServiceProvider();
        $this->container = new Container();
        $this->container->set('config', new AppConfig([
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
        ]));

        $this->obj->register($this->container);
    }

    /**
     * @return void
     */
    protected function resetDefaultLanguage()
    {
        $this->container->set('locales/default-language', function (Container $container) {
            $localesConfig = $container->get('locales/config');
            if (isset($localesConfig['auto_detect']) && $localesConfig['auto_detect']) {
                if ($container->get('locales/browser-language') !== null) {
                    return $container->get('locales/browser-language');
                }
            }
            return $localesConfig['default_language'];
        });
    }

    /**
     * @return void
     */
    public function testKeys()
    {
        $this->assertFalse($this->container->has('foofoobarbarbaz'));
        $this->assertTrue($this->container->has('locales/config'));
        $this->assertTrue($this->container->has('locales/available-languages'));
        $this->assertTrue($this->container->has('locales/default-language'));
        $this->assertTrue($this->container->has('locales/browser-language'));
        $this->assertTrue($this->container->has('translator'));
        $this->assertTrue($this->container->has('middlewares/charcoal/translator/middleware/language'));
    }

    /**
     * @return void
     */
    public function testAvailableLanguages()
    {
        $languages = $this->container->get('locales/available-languages');
        $this->assertContains('en', $languages);
    }

    /**
     * @return void
     */
    public function testLanguages()
    {
        $languages = $this->container->get('locales/languages');
        $this->assertArrayHasKey('en', $languages);
    }

    /**
     * @return void
     */
    public function testDefaultLanguage()
    {
        $defaultLanguage = $this->container->get('locales/default-language');
        $this->assertEquals('en', $defaultLanguage);
    }

    /**
     * @return void
     */
    public function testBrowserLanguageIsNullWithoutHttp()
    {
        $browserLanguage = $this->container->get('locales/browser-language');
        $this->assertNull($browserLanguage);
    }

    /**
     * @return void
     */
    public function testBrowserLanguage()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr';
        $browserLanguage = $this->container->get('locales/browser-language');
        $this->assertEquals('fr', $browserLanguage);
    }

    /**
     * @return void
     */
    public function testBrowserLanguageIsNullIfInvalidHttp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'baz';
        $browserLanguage = $this->container->get('locales/browser-language');
        $this->assertNull($browserLanguage);
    }

    /**
     * @return void
     */
    public function testDetectedLanguageIsNullWithoutHttp()
    {
        $this->container->get('locales/config')->setAutoDetect(true);

        $this->resetDefaultLanguage();

        $defaultLanguage = $this->container->get('locales/default-language');
        $this->assertEquals('en', $defaultLanguage);

        $this->container->get('locales/config')->setAutoDetect(false);
    }

    /**
     * @return void
     */
    public function testDetectedLanguage()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr';
        $this->container->get('locales/config')->setAutoDetect(true);

        $this->resetDefaultLanguage();

        $defaultLanguage = $this->container->get('locales/default-language');
        $this->assertEquals('fr', $defaultLanguage);

        $this->container->get('locales/config')->setAutoDetect(false);
    }

    /**
     * @return void
     */
    public function testFallbackLanguages()
    {
        $fallbackLanguages = $this->container->get('locales/fallback-languages');
        $this->assertEquals([ 'en' ], $fallbackLanguages);
    }

    /**
     * @return void
     */
    public function testLanguageManager()
    {
        $manager = $this->container->get('locales/manager');
        $this->assertInstanceOf(LocalesManager::class, $manager);
    }

    /**
     * @return void
     */
    public function testTranslator()
    {
        $translator = $this->container->get('translator');
        $this->assertInstanceOf(Translator::class, $translator);
    }

    /**
     * @return void
     */
    public function testMiddleware()
    {
        $middleware = $this->container->get('middlewares/charcoal/translator/middleware/language');
        $this->assertInstanceOf(LanguageMiddleware::class, $middleware);
    }
}
