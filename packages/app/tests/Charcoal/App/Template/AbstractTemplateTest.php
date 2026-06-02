<?php

namespace Charcoal\Tests\App\Template;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Slim
use Slim\Http\Response;

// From Pimple
use Pimple\Container;

// From 'charcoal-app'
use Charcoal\App\Template\AbstractTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\App\ContainerProvider;

/**
 *
 */
class AbstractTemplateTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\App\Template\AbstractTemplate $obj;

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

        $this->obj = new class ([
            'logger'    => $container['logger'],
            'container' => $container
        ]) extends AbstractTemplate {};
    }

    public function testInitIsTrue(): void
    {
        $request = $this->createStub(RequestInterface::class);
        $this->assertTrue($this->obj->init($request));
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
