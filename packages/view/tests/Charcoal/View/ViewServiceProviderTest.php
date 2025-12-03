<?php

namespace Charcoal\Tests\View;

use DI\Container;
use Nyholm\Psr7\Response;
use Charcoal\App\AppConfig;
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
// From 'charcoal-view'
use Charcoal\View\ViewServiceProvider;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Nyholm\Psr7\Uri;
use Charcoal\Translator\LocalesManager;
use Psr\Container\ContainerInterface;
use Charcoal\Translator\Translator;

#[CoversClass(ViewServiceProvider::class)]
class ViewServiceProviderTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testProvider()
    {
        $container = new Container([
            'config' => [],
        ]);

        $this->registerBaseServices($container);

        $provider = new ViewServiceProvider();
        $provider->register($container);

        $this->assertTrue($container->has('view/config'));
        $this->assertTrue($container->has('view/engine'));
        $this->assertTrue($container->has('view/renderer'));
        $this->assertTrue($container->has('view'));
    }

    /**
     * @return void
     */
    public function testExtraViewPaths()
    {
        $container = new Container([
            'config' => [
                'base_path' => dirname(__DIR__, 3),
            ],
            'module/classes' => [
                'Charcoal\\Tests\\View\\Mock\\MockModule',
            ],
        ]);

        $this->registerBaseServices($container);

        $provider = new ViewServiceProvider();
        $provider->register($container);

        $viewConfig = $container->get('view/config');
        $this->assertContains('tests/Charcoal/View/Mock/templates', $viewConfig->paths());
    }

    /**
     * @return void
     */
    public function testProviderTwig()
    {
        $container = new Container([
            'debug' => false,
            'config' => new AppConfig([
                'base_path' => __DIR__,
                'view'      => [
                    'paths'          => [ 'Twig/templates' ],
                    'default_engine' => 'twig',
                ]
            ]),
        ]);

        $this->registerBaseServices($container);

        $provider = new ViewServiceProvider();
        $provider->register($container);

        $provider = new TranslatorServiceProvider();
        $provider->register($container);

        $ret = $container->get('view')->render('foo', [ 'foo' => 'Bar' ]);
        $this->assertEquals('Hello Bar', trim($ret));

        $response = new Response();
        $ret = $container->get('view/renderer')->render($response, 'foo', [ 'foo' => 'Baz' ]);
        $this->assertEquals('Hello Baz', trim((string)$ret->getBody()));
    }

    /**
     * @return void
     */
    public function testProviderMustache()
    {
        $container = new Container([
            'translator' => null,
            'config'     => new AppConfig([
                'base_path' => __DIR__,
                'view'      => [
                    'paths'          => [ 'Mustache/templates' ],
                    'default_engine' => 'mustache',
                ]
            ]),
        ]);

        $this->registerBaseServices($container);

        $provider = new ViewServiceProvider();
        $provider->register($container);

        $ret = $container->get('view')->render('foo', [ 'foo' => 'Bar' ]);
        $this->assertEquals('Hello Bar', trim($ret));

        $response = new Response();
        $ret = $container->get('view/renderer')->render($response, 'foo', [ 'foo' => 'Baz' ]);
        $this->assertEquals('Hello Baz', trim((string)$ret->getBody()));
    }

    /**
     * @return void
     */
    public function testProviderPhp()
    {
        $container = new Container([
            'config' => new AppConfig([
                'base_path' => __DIR__,
                'view'      => [
                    'paths'          => [ 'Php/templates' ],
                    'default_engine' => 'php',
                ]
            ]),
        ]);

        $this->registerBaseServices($container);

        $provider = new ViewServiceProvider();
        $provider->register($container);

        $ret = $container->get('view')->render('foo', [ 'foo' => 'Bar' ]);
        $this->assertEquals('Hello Bar', trim($ret));

        $response = new Response();
        $ret = $container->get('view/renderer')->render($response, 'foo', [ 'foo' => 'Baz' ]);
        $this->assertEquals('Hello Baz', trim((string)$ret->getBody()));
    }

    /**
     * Setup the application's base URI.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerBaseUrl(Container $container)
    {
        $container->set('base-url', function () {
            return (new Uri(''));
        });

        $container->set('admin/base-url', function () {
            return (new Uri('admin'));
        });
    }

    public function registerBaseServices(ContainerInterface $container)
    {
        $this->registerDebug($container);
        $this->registerBaseUrl($container);
        $this->registerTranslator($container);
    }

    /**
     * Setup the application's translator service.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerTranslator(Container $container)
    {
        $container->set('locales/manager', function (Container $container) {
            return new LocalesManager([
                'locales' => [
                    'en' => [ 'locale' => 'en-US' ]
                ]
            ]);
        });

        $container->set('translator', function (Container $container) {
            return new Translator([
                'manager' => $container->get('locales/manager')
            ]);
        });
    }

    /**
     * Register the unit tests required services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerDebug(Container $container)
    {
        if (!($container->has('debug'))) {
            $container->set('debug', false);
        }
    }
}
