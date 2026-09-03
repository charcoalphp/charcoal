<?php

namespace Charcoal\Tests\App\Handler;

use Pimple\Container;

use Slim\Http\Environment;
use Slim\Http\Request;

use Charcoal\Factory\GenericFactory as Factory;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\App\ContainerProvider;
use Charcoal\View\ViewInterface;

/**
 * Shared helpers for handler tests.
 */
abstract class HandlerTestCase extends AbstractTestCase
{
    public static function container()
    {
        $container = new Container();
        $provider  = new ContainerProvider();
        $provider->registerLogger($container);
        $provider->registerTranslator($container);
        $provider->registerConfig($container);

        $container['settings'] = [
            'displayErrorDetails' => false,
        ];

        $view = \Mockery::mock(ViewInterface::class);
        $view->shouldReceive('renderTemplate')->andReturnUsing(function ($template) {
            return $template;
        });
        $view->shouldReceive('render')->andReturnUsing(function ($template) {
            return (string)$template;
        });
        $view->shouldReceive('setDynamicTemplate')->andReturnNull();
        $container['view'] = $view;

        $container['template/factory'] = function ($c) {
            return new Factory([
                'arguments' => [[
                    'logger'    => $c['logger'],
                    'container' => $c,
                ]],
            ]);
        };

        return $container;
    }

    public static function request($path = '/', array $env = [])
    {
        return Request::createFromEnvironment(Environment::mock(array_merge([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => $path,
        ], $env)));
    }
}
