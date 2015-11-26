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
        $this->assertEquals('', $this->obj->resolver_prefix());
        $ret = $this->obj->set_resolver_prefix('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->resolver_prefix());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->set_resolver_prefix(false);
    }

    public function testSetResolverSuffix()
    {
        $this->assertEquals('', $this->obj->resolver_suffix());
        $ret = $this->obj->set_resolver_suffix('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->resolver_suffix());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->set_resolver_suffix(false);
    }

    public function testSetResolverCapitals()
    {
        //$this->assertEquals([], $this->obj->resolver_capitals());
        $ret = $this->obj->set_resolver_capitals(['$']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['$'], $this->obj->resolver_capitals());

        $this->assertEquals('\$Abc$De', $this->obj->resolve('$abc$de'));
    }

    public function testSetResoverReplacements()
    {
        $ret = $this->obj->set_resolver_replacements(['$'=>'_']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['$'=>'_'], $this->obj->resolver_replacements());

        $this->assertEquals('\_abc_de', $this->obj->resolve('$abc$de'));
    }

    /**
    * @dataProvider providerResolve
    */
    public function testResolve($type, $classname)
    {
        $this->assertEquals($classname, $this->obj->resolve($type));
        
        // Test with additional prefix / suffix
        $this->obj->set_resolver_suffix('Test');
        $this->assertEquals($classname.'Test', $this->obj->resolve($type));
    }

    public function testResolveWithoutStringThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->resolve(false);
    }

    public function testIsResolvable()
    {
        $this->assertFalse($this->obj->is_resolvable('foo'));
        $this->assertTrue($this->obj->is_resolvable('charcoal/factory/map-factory'));

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->is_resolvable(false);
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
