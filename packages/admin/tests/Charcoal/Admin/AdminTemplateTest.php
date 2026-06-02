<?php

namespace Charcoal\Tests\Admin;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class AdminTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Admin\AdminTemplate $obj;

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

        $this->obj = new AdminTemplate([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    public function testSetIdent(): void
    {
        $this->assertNull($this->obj->ident());
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($this->obj, $ret);
        $this->assertEquals('foobar', $this->obj->ident());
    }

    public function testSetLabel(): void
    {
        $this->assertNull($this->obj->label());
        $ret = $this->obj->setLabel('foobar');
        $this->assertSame($this->obj, $ret);
        $this->assertEquals('foobar', (string)$this->obj->label());
    }

    public function testAuthRequiredIsTrue(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertTrue($res);
    }

    /**
     * Set up the service container.
     */
    protected function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerTemplateDependencies($container);

            $container['widget/factory'] = $this->createMock(\Charcoal\Factory\FactoryInterface::class);

            $this->container = $container;
        }

        return $this->container;
    }
}
