<?php

namespace Charcoal\Tests\Admin\Property\Input;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\TextInput;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class TextInputTest extends AbstractTestCase
{
    private \Charcoal\Admin\Property\Input\TextInput $obj;

    public function setUp(): void
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerInputDependencies($container);

        $this->obj = new TextInput([
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader'],
            'container'       => $container,
        ]);
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'size'        => 42,
            'min_length'  => 10,
            'max_length'  => 100,
            'pattern'     => 'foo',
            'placeholder' => 'bar'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->size());
        $this->assertEquals(10, $obj->minLength());
        $this->assertEquals(100, $obj->maxLength());
        $this->assertEquals('foo', $obj->pattern());
        $this->assertEquals('bar', (string)$obj->placeholder());
    }

    public function testSetSize(): void
    {
        $obj = $this->obj;
        $ret = $obj->setSize(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->size());

        $this->expectException('\InvalidArgumentException');
        $obj->setSize(false);
    }

    public function testSetMinLength(): void
    {
        $obj = $this->obj;
        $ret = $obj->setMinLength(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->minLength());

        $this->expectException('\InvalidArgumentException');
        $obj->setMinLength(false);
    }

    public function testSetMaxLength(): void
    {
        $obj = $this->obj;
        $ret = $obj->setMaxLength(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->maxLength());

        $this->expectException('\InvalidArgumentException');
        $obj->setMaxLength(false);
    }

    public function testSetPattern(): void
    {
        $obj = $this->obj;
        $ret = $obj->setPattern('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->pattern());

        $this->expectException('\InvalidArgumentException');
        $obj->setPattern(false);
    }

    public function testSetPlaceholder(): void
    {
        $obj = $this->obj;
        $ret = $obj->setPlaceholder('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', (string)$obj->placeholder());
    }
}
