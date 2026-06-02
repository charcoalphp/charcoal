<?php

namespace Charcoal\Tests\App\Route;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-app'
use Charcoal\App\Route\ScriptRoute;
use Charcoal\App\Route\ScriptRouteConfig;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\App\ContainerProvider;

/**
 *
 */
class ScriptRouteTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\App\Route\ScriptRoute $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $this->obj = new ScriptRoute([
            'config' => new ScriptRouteConfig([
                'controller' => 'foo/bar'
            ])
        ]);
    }

    public function testInvoke(): void
    {
        $container = $this->container();

        $container['script/factory'] = (fn($c): \Charcoal\Factory\GenericFactory => new Factory());

        $request  = $this->createStub(RequestInterface::class);
        $response = $this->createStub(ResponseInterface::class);

        // Invalid because "foo/bar" is not a valid script controller
        $this->expectException('\Exception');
        call_user_func($this->obj->__invoke(...), $container, $request, $response);
    }

    /**
     * Set up the service container.
     */
    private function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerLogger($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
