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

    protected function setUp(): void
    {
        $this->obj = new ResolverFactory();
    }

    public function testSetResolverPrefix(): void
    {
        $this->assertEquals('', $this->obj->resolverPrefix());
        $ret = $this->obj->setResolverPrefix('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->resolverPrefix());

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setResolverPrefix(false);
    }

    public function testSetResolverSuffix(): void
    {
        $this->assertEquals('', $this->obj->resolverSuffix());
        $ret = $this->obj->setResolverSuffix('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->resolverSuffix());

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setResolverSuffix(false);
    }

    public function testSetResolverCapitals(): void
    {
        $ret = $this->obj->setResolverCapitals(['$']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['$'], $this->obj->resolverCapitals());

        $this->assertEquals('\$Abc$De', $this->obj->resolve('$abc$de'));
    }

    public function testSetResoverReplacements(): void
    {
        $ret = $this->obj->setResolverReplacements(['$'=>'_']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['$'=>'_'], $this->obj->resolverReplacements());

        $this->assertEquals('\_abc_de', $this->obj->resolve('$abc$de'));
    }

    /**
     *
     * @param  string $type      Factory key.
     * @param  string $classname Factory class name.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerResolve')]
    public function testResolve(string $type, string $classname): void
    {
        $this->assertEquals($classname, $this->obj->resolve($type));

        // Test with additional prefix / suffix
        $this->obj->setResolverSuffix('Test');
        $this->assertEquals($classname.'Test', $this->obj->resolve($type));
    }

    public function testResolveWithoutStringThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->resolve(false);
    }

    public function testIsResolvable(): void
    {
        $this->assertFalse($this->obj->isResolvable('foo'));
        $this->assertTrue($this->obj->isResolvable('charcoal/factory/map-factory'));

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->isResolvable(false);
    }

    public function testCreate(): void
    {
        $ret = $this->obj->create('charcoal/factory/map-factory');
        $this->assertInstanceOf(\Charcoal\Factory\MapFactory::class, $ret);
    }

    public static function providerResolve(): array
    {
        return [
            ['foo', '\Foo'],
            ['foo/bar', '\Foo\Bar'],
            ['\Foo\Bar', '\Foo\Bar'],
            ['foo-bar', '\FooBar'],
            ['foo.bar', '\Foo_Bar'],
            ['foo.bar\baz_baz-baz/foo\\', '\Foo_Bar\Baz_BazBaz\Foo'],
            ['charcoal/factory/map-factory', \Charcoal\Factory\MapFactory::class]
        ];
    }
}
