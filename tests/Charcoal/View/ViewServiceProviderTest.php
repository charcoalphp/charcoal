<?php

namespace Charcoal\Tests\View;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Slim\Http\Response;

use Pimple\Container;

use Charcoal\View\ViewServiceProvider;

/**
 *
 */
class ViewServiceProviderTest extends PHPUnit_Framework_TestCase
{
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
            ]
        ]);
        $provider = new ViewServiceProvider();
        $provider->register($container);

        $ret = $container['view']->render('foo', [ 'foo' => 'Bar' ]);
        $this->assertEquals('Hello Bar', trim($ret));

        $response = new Response();
        $ret = $container['view/renderer']->render($response, 'foo', ['foo'=>'Baz']);
        $this->assertEquals('Hello Baz', trim((string)$ret->getBody()));
    }

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
