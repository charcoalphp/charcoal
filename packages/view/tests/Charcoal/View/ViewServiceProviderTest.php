<?php

namespace Charcoal\Tests\View;

// From Slim
use Charcoal\App\AppConfig;
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Slim\Http\Response;

// From Pimple
use Pimple\Container;

// From 'charcoal-view'
use Charcoal\View\ViewServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ViewServiceProviderTest extends AbstractTestCase
{
    public function testProvider(): void
    {
        $container = new Container([
            'config' => [],
        ]);

        $provider = new ViewServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['view/config']));
        $this->assertTrue(isset($container['view/engine']));
        $this->assertTrue(isset($container['view/renderer']));
        $this->assertTrue(isset($container['view']));
    }

    public function testExtraViewPaths(): void
    {
        $container = new Container([
            'config' => [
                'base_path' => dirname(__DIR__, 3),
            ],
            'module/classes' => [
                \Charcoal\Tests\View\Mock\MockModule::class,
            ],
        ]);

        $provider = new ViewServiceProvider();
        $provider->register($container);

        $viewConfig = $container['view/config'];
        $this->assertContains('tests/Charcoal/View/Mock/templates', $viewConfig->paths());
    }

    public function testProviderTwig(): void
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
        $provider = new ViewServiceProvider();
        $provider->register($container);

        $provider = new TranslatorServiceProvider();
        $provider->register($container);

        $ret = $container['view']->render('foo', [ 'foo' => 'Bar' ]);
        $this->assertEquals('Hello Bar', trim((string) $ret));

        $response = new Response();
        $ret = $container['view/renderer']->render($response, 'foo', [ 'foo' => 'Baz' ]);
        $this->assertEquals('Hello Baz', trim((string)$ret->getBody()));
    }

    public function testProviderMustache(): void
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
        $provider = new ViewServiceProvider();
        $provider->register($container);

        $ret = $container['view']->render('foo', [ 'foo' => 'Bar' ]);
        $this->assertEquals('Hello Bar', trim((string) $ret));

        $response = new Response();
        $ret = $container['view/renderer']->render($response, 'foo', [ 'foo' => 'Baz' ]);
        $this->assertEquals('Hello Baz', trim((string)$ret->getBody()));
    }

    public function testProviderPhp(): void
    {
        $container = new Container([
            'config' => new AppConfig ([
                'base_path' => __DIR__,
                'view'      => [
                    'paths'          => [ 'Php/templates' ],
                    'default_engine' => 'php',
                ]
            ]),
        ]);
        $provider = new ViewServiceProvider();
        $provider->register($container);

        $ret = $container['view']->render('foo', [ 'foo' => 'Bar' ]);
        $this->assertEquals('Hello Bar', trim((string) $ret));

        $response = new Response();
        $ret = $container['view/renderer']->render($response, 'foo', [ 'foo' => 'Baz' ]);
        $this->assertEquals('Hello Baz', trim((string)$ret->getBody()));
    }
}
