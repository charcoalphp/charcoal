<?php

namespace Charcoal\Tests\Admin\Property\Input;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\TextareaInput;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class TextareaInputTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\Admin\Property\Input\TextareaInput $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = new TextareaInput([
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader'],
        ]);
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'cols'=>42,
            'rows'=>84
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->cols());
        $this->assertEquals(84, $obj->rows());
    }

    public function testSetCols(): void
    {
        $obj = $this->obj;
        $ret = $obj->setCols(42);

        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->cols());

        $this->expectException('\InvalidArgumentException');
        $obj->setCols('foo');
    }

    public function testSetRows(): void
    {
        $obj = $this->obj;
        $ret = $obj->setRows(42);

        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->rows());

        $this->expectException('\InvalidArgumentException');
        $obj->setRows('foo');
    }

    /**
     * Set up the service container.
     */
    protected function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerInputDependencies($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
