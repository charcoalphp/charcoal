<?php

namespace Charcoal\Tests\View;

// From PSR-3
use Psr\Log\NullLogger;

// From Slim
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
    /**
     * @return void
     */
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);

        $provider = new ViewServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['view/config']));
        $this->assertTrue(isset($container['view/engine']));
        $this->assertTrue(isset($container['view/renderer']));
        $this->assertTrue(isset($container['view']));
    }

    /**
     * @return void
     */
    public function testProviderTwig()
    {
        $container = new Container([
            'config' => [
                'base_path' => __DIR__,
                'view' => [
                    'paths' => ['Twig/templates'],
                    'default_engine' => 'twig'
                ]
            ],
            'module/classes' => [],
            'logger' => new NullLogger()
        ]);
        $provider = new ViewServiceProvider();
        $provider->register($container);

        $ret = $container['view']->render('foo', ['foo'=>'Bar']);
        $this->assertEquals('Hello Bar', trim($ret));

        $response = new Response();
        $ret = $container['view/renderer']->render($response, 'foo', ['foo'=>'Baz']);
        $this->assertEquals('Hello Baz', trim((string)$ret->getBody()));
    }

    /**
     * @return void
     */
    public function testProviderMustache()
    {
        $container = new Container([
            'logger' => new NullLogger(),
            'translator' => null,
            'config' => [
                'base_path' => __DIR__,
                'view' => [
                    'paths' => [ 'Mustache/templates' ],
                    'default_engine' => 'mustache'
                ]
            ],
            'module/classes' => []
        ]);
        $provider = new ViewServiceProvider();
        $provider->register($container);

        $ret = $container['view']->render('foo', [ 'foo' => 'Bar' ]);
        $this->assertEquals('Hello Bar', trim($ret));

        $response = new Response();
        $ret = $container['view/renderer']->render($response, 'foo', ['foo'=>'Baz']);
        $this->assertEquals('Hello Baz', trim((string)$ret->getBody()));
    }

    /**
     * @return void
     */
    public function testProviderPhp()
    {
        $container = new Container([
            'config' => [
                'base_path' => __DIR__,
                'view' => [
                    'paths' => ['Php/templates'],
                    'default_engine' => 'php'
                ]
            ],
            'module/classes' => [],
            'logger' => new NullLogger()
        ]);
        $provider = new ViewServiceProvider();
        $provider->register($container);

        $ret = $container['view']->render('foo', ['foo'=>'Bar']);
        $this->assertEquals('Hello Bar', trim($ret));

        $response = new Response();
        $ret = $container['view/renderer']->render($response, 'foo', ['foo'=>'Baz']);
        $this->assertEquals('Hello Baz', trim((string)$ret->getBody()));
    }
}
