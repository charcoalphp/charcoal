<?php

namespace Charcoal\Tests\Core;

use \Charcoal\Factory\AbstractFactory;

use \Charcoal\Tests\Factory\AbstractFactoryClass as AbstractFactoryClass;

/**
 *
 */
class ResolverFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $this->obj = new \Charcoal\Factory\ResolverFactory();
    }

    public function testSetResolverPrefix()
    {
        $this->assertEquals('', $this->obj->resolverPrefix());
        $ret = $this->obj->setResolverPrefix('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->resolverPrefix());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setResolverPrefix(false);
    }

    public function testSetResolverSuffix()
    {
        $this->assertEquals('', $this->obj->resolverSuffix());
        $ret = $this->obj->setResolverSuffix('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->resolverSuffix());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setResolverSuffix(false);
    }

    public function testSetResolverCapitals()
    {
        //$this->assertEquals([], $this->obj->resolverCapitals());
        $ret = $this->obj->setResolverCapitals(['$']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['$'], $this->obj->resolverCapitals());

        $this->assertEquals('\$Abc$De', $this->obj->resolve('$abc$de'));
    }

    public function testSetResoverReplacements()
    {
        $ret = $this->obj->setResolverReplacements(['$'=>'_']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['$'=>'_'], $this->obj->resolverReplacements());

        $this->assertEquals('\_abc_de', $this->obj->resolve('$abc$de'));
    }

    /**
     * @dataProvider providerResolve
     */
    public function testResolve($type, $classname)
    {
        $this->assertEquals($classname, $this->obj->resolve($type));

        // Test with additional prefix / suffix
        $this->obj->setResolverSuffix('Test');
        $this->assertEquals($classname.'Test', $this->obj->resolve($type));
    }

    public function testResolveWithoutStringThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->resolve(false);
    }

    public function testIsResolvable()
    {
        $this->assertFalse($this->obj->isResolvable('foo'));
        $this->assertTrue($this->obj->isResolvable('charcoal/factory/map-factory'));

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->isResolvable(false);
    }

    public function testCreate()
    {
        $ret = $this->obj->create('charcoal/factory/map-factory');
        $this->assertInstanceOf('\Charcoal\Factory\MapFactory', $ret);
    }

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
