<?php

namespace Charcoal\Tests\Admin;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class AdminWidgetTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\Admin\AdminWidget $obj;

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

        $this->obj = new AdminWidget([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'type'         => 'foo',
            'ident'        => 'bar',
            'label'        => 'baz',
            'show_actions' => false
        ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->type());
        $this->assertEquals('bar', $obj->ident());
        $this->assertEquals('baz', $obj->label());
        $this->assertNotTrue($obj->showActions());
    }

    public function testSetType(): void
    {
        $obj = $this->obj;
        $this->assertEquals(null, $obj->type());

        $ret = $obj->setType('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->type());

        $this->expectException('\InvalidArgumentException');
        $obj->setType(1);
    }

    public function testSetLabel(): void
    {
        //$this->assertEquals(null, $obj->label());

        $obj = $this->obj;
        $obj->setIdent('foo.bar');
        $this->assertEquals(null, $obj->label());

        $obj->setLabel('Foo Bar');
        $this->assertEquals('Foo Bar', $obj->label());

        //$this->expectException('\InvalidArgumentException');
        //$obj->set_label(null);
    }

    /**
     * Set up the service container.
     */
    protected function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerWidgetDependencies($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
