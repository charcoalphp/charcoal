<?php

namespace Charcoal\Tests\App\Route;

// From Pimple
use Pimple\Container;

// From 'charcoal-app'
use Charcoal\App\Route\ActionRoute;
use Charcoal\Tests\App\ContainerProvider;
use Charcoal\Tests\AbstractTestCase;

class ActionRouteTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\App\Route\ActionRoute $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = new ActionRoute([
            'logger' => $container['logger'],
            'config' => []
        ]);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(ActionRoute::class, $this->obj);
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
