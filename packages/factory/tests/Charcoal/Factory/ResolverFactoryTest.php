<?php

namespace Charcoal\Tests\Factory;

use Charcoal\Factory\ResolverFactory;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ResolverFactoryTest extends AbstractTestCase
{
    /**
     * @var ResolverFactory
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = new ResolverFactory();
    }

    /**
     * @return void
     */
    public function testSetResolverPrefix()
    {
        $this->assertEquals('', $this->obj->resolverPrefix());
        $ret = $this->obj->setResolverPrefix('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->resolverPrefix());

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setResolverPrefix(false);
    }

    /**
     * @return void
     */
    public function testSetResolverSuffix()
    {
        $this->assertEquals('', $this->obj->resolverSuffix());
        $ret = $this->obj->setResolverSuffix('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->resolverSuffix());

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setResolverSuffix(false);
    }

    /**
     * @return void
     */
    public function testSetResolverCapitals()
    {
        $ret = $this->obj->setResolverCapitals(['$']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['$'], $this->obj->resolverCapitals());

        $this->assertEquals('\$Abc$De', $this->obj->resolve('$abc$de'));
    }

    /**
     * @return void
     */
    public function testSetResoverReplacements()
    {
        $ret = $this->obj->setResolverReplacements(['$'=>'_']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['$'=>'_'], $this->obj->resolverReplacements());

        $this->assertEquals('\_abc_de', $this->obj->resolve('$abc$de'));
    }

    /**
     * @dataProvider providerResolve
     *
     * @param  string $type      Factory key.
     * @param  string $classname Factory class name.
     * @return void
     */
    public function testResolve($type, $classname)
    {
        $this->assertEquals($classname, $this->obj->resolve($type));

        // Test with additional prefix / suffix
        $this->obj->setResolverSuffix('Test');
        $this->assertEquals($classname.'Test', $this->obj->resolve($type));
    }

    /**
     * @return void
     */
    public function testResolveWithoutStringThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->resolve(false);
    }

    /**
     * @return void
     */
    public function testIsResolvable()
    {
        $this->assertFalse($this->obj->isResolvable('foo'));
        $this->assertTrue($this->obj->isResolvable('charcoal/factory/map-factory'));

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->isResolvable(false);
    }

    /**
     * @return void
     */
    public function testCreate()
    {
        $ret = $this->obj->create('charcoal/factory/map-factory');
        $this->assertInstanceOf('\Charcoal\Factory\MapFactory', $ret);
    }

    /**
     * @return array
     */
    public function providerResolve()
    {
        return [
            ['foo', '\Foo'],
            ['foo/bar', '\Foo\Bar'],
            ['\Foo\Bar', '\Foo\Bar'],
            ['foo-bar', '\FooBar'],
            ['foo.bar', '\Foo_Bar'],
            ['foo.bar\baz_baz-baz/foo\\', '\Foo_Bar\Baz_BazBaz\Foo'],
            ['charcoal/factory/map-factory', '\Charcoal\Factory\MapFactory']
        ];
    }
}
