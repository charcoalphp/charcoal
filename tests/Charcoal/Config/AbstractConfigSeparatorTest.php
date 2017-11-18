<?php

namespace Charcoal\Tests\Config;

use PHPUnit_Framework_TestCase;

use Exception;
use InvalidArgumentException;

use Charcoal\Config\AbstractConfig;

/**
 * Test the separator functionalities of AbstractConfig.
 */
class AbstractConfigSeparatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var mixed The Abstract Config mock
     */
    public $obj;

    public function setUp()
    {
        include_once 'AbstractEntityClass.php';
        $this->obj = $this->getMockForAbstractClass(AbstractConfig::class);
    }

    public function testGetWithSeparatorNullValue()
    {
        $this->assertNull($this->obj->get('foo.bar'));
    }

    public function testGetWithSeparator()
    {
        $this->obj->set('foo', ['bar'=>42]);
        $this->assertEquals(['bar'=>42], $this->obj->get('foo'));
        $this->assertEquals(42, $this->obj->get('foo.bar'));
    }

    public function testSetWithSeparator()
    {
        $this->obj->set('foo.bar', 42);
        $this->assertEquals(['bar'=>42], $this->obj->get('foo'));
        $this->assertEquals(42, $this->obj->get('foo.bar'));

        $this->obj->set('foo.bar', 13);
        $this->assertEquals(13, $this->obj->get('foo.bar'));

        $this->obj->set('foo.baz', 666);
        $this->assertEquals(13, $this->obj->get('foo.bar'));
        $this->assertEquals(666, $this->obj->get('foo.baz'));

        $this->obj->set('foo.x.y.z', 'test');
        $this->assertEquals('test', $this->obj->get('foo.x.y.z'));
    }

    public function testSetWithSeparatorExistingArray()
    {
        $this->obj->setData([
           'x' => [
               'a' => 'foo',
               'y' => [
                   'z' => 42
               ]
           ]
        ]);

        $this->assertEquals('foo', $this->obj->get('x.a'));
        $this->obj['x.a'] = 'bar';
        $this->assertEquals('bar', $this->obj->get('x.a'));

        $this->assertEquals(42, $this->obj['x.y.z']);
        $this->obj['x.y.z'] = 666;
        $this->assertEquals(666, $this->obj['x.y.z']);

        $this->obj['x.b'] = 'foobar';
        $this->assertEquals('foobar', $this->obj['x.b']);
        $this->assertEquals('bar', $this->obj['x.a']);
        $this->assertEquals(666, $this->obj['x.y.z']);
    }

    public function testGetWithCustomSeparator()
    {
        $obj = $this->obj;
        $obj->set('foo', ['bar'=>42]);
        $obj->setSeparator('/');
        $this->assertEquals(42, $obj->get('foo/bar'));
    }

    public function testOffsetGetWithCustomSeparator()
    {
        $obj = $this->obj;
        $obj->set('foo', ['bar'=>42]);
        $obj->setSeparator('/');
        $this->assertEquals(42, $obj['foo/bar']);
    }

    public function testBaseSeparator()
    {
        $this->obj->setData([
            'foo' => [
                'bar' => 42,
                'baz' => [
                    'foo' => 2
                ]
            ]
        ]);
        $this->assertNull($this->obj['foo/bar']);
        $this->assertFalse($this->obj->has('foo/baz'));

        $this->obj->setSeparator('.');
        $this->assertTrue($this->obj->has('foo.baz'));
        $this->assertTrue($this->obj->has('foo.baz.foo'));
        $this->assertEquals(42, $this->obj['foo.bar']);
        $this->assertEquals(['foo'=>2], $this->obj['foo.baz']);
        $this->assertEquals(2, $this->obj->get('foo.baz.foo'));

        // Asserts that calling by separator is the same as calling by arrays
        $this->assertEquals($this->obj['foo.bar'], $this->obj['foo']['bar']);
        $this->assertEquals($this->obj['foo.baz.foo'], $this->obj['foo']['baz']['foo']);

        $this->obj->setSeparator('/');
        $this->assertFalse($this->obj->has('foo.baz'));
        $this->assertTrue($this->obj->has('foo/baz'));
    }

    public function testSeparatorSetWithSeparatedStringOrArrayResultsSameConfig()
    {
        $config1 = $this->getMockForAbstractClass(AbstractConfig::class, [[
            'a' => [
                'foo' => 42
            ]
        ]]);
        $config2 = $this->getMockForAbstractClass(AbstractConfig::class, [[
            'a.foo' => 42
        ]]);

        $this->assertEquals($config1, $config2);
        $this->assertEquals($config1['a.foo'], $config2['a.foo']);
        $this->assertEquals($config1['a'], $config2['a']);
    }


}
