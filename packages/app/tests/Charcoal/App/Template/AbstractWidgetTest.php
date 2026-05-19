<?php

namespace Charcoal\Tests\App\Template;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Slim
use Slim\Http\Response;

// From Pimple
use Pimple\Container;

// From 'charcoal-app'
use Charcoal\App\Template\AbstractWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\App\ContainerProvider;

/**
 *
 */
class AbstractWidgetTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\App\Template\AbstractWidget $obj;

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

        $this->obj = $this->getMockForAbstractClass(AbstractWidget::class, [[
            'logger'    => $container['logger'],
            'container' => $container
        ]]);
    }

    /**
     * Assert that the widget:
     * - active default state is true
     * - `setActive()` method is chainable
     * - `setActive()` actually sets the active value.
     */
    public function testSetActive(): void
    {
        $obj = $this->obj;
        $this->assertTrue($obj->active());
        $ret = $obj->setActive(false);
        $this->assertSame($ret, $obj);
        $this->assertFalse($obj->active());
    }

    /**
     * Set up the service container.
     */
    private function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerConfig($container);
            $containerProvider->registerCache($container);
            $containerProvider->registerLogger($container);
            $containerProvider->registerView($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
