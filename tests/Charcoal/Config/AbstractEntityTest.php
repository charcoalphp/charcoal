<?php

namespace Charcoal\Tests\Config;

use PHPUnit_Framework_TestCase;

use Charcoal\Tests\Config\AbstractEntityClass;

use InvalidArgumentException;

use Charcoal\Config\AbstractEntity;

/**
 *
 */
class AbstractEntityTest extends PHPUnit_Framework_TestCase
{
    /**
     * The object under test
     * @var AbstractEntity
     */
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass(AbstractEntity::class);
    }

    /**
     * Asserts that
     * - keys are empty by default
     * - keys are added automatically when setting a value
     * - keys are removed automatically when unsetting a value
     */
    public function testKeys()
    {
        $obj = $this->obj;
        $this->assertEquals([], $obj->keys());

        $obj->set('foobar', 42);
        $this->assertEquals(['foobar'], $obj->keys());

        unset($obj['foobar']);
        $this->assertEquals([], $obj->keys());
    }

    public function testSetDataIsChainable()
    {
        $ret = $this->obj->setData([
           'foo' => 'bar'
        ]);
        $this->assertSame($ret, $this->obj);
    }

    public function testSetDataSetsData()
    {
        $this->obj->setData([
            'foo' => 'bar'
        ]);
        $this->assertEquals(['foo'=>'bar'], $this->obj->data());
    }

    /**
     * Asserts that the `set()` method:
     * - sets the value
     * and that the `get()` method:
     * - gets the value
     */
    public function testSetGet()
    {
        $this->assertNull($this->obj->get('foobar'));

        $this->obj->set('foobar', 42);
        $this->assertEquals(42, $this->obj->get('foobar'));
    }

    public function testSetUnderscore()
    {
        $this->obj->set('_', 'test');
        $this->assertNull($this->obj->get('_'));
    }

    public function testSetGetWithSetterGetter()
    {
        $obj = new AbstractEntityClass();
        $obj->set('foo', 2);
        $this->assertEquals('foo is 12', $obj->get('foo'));
    }

    public function testHas()
    {
        $this->assertFalse($this->obj->has('foo'));

        $this->obj['foo'] = 'bar';
        $this->assertTrue($this->obj->has('foo'));

        unset($this->obj['foo']);
        $this->assertFalse($this->obj->has('foo'));
    }

    public function testHasWithSetterGetter()
    {
        $obj = new AbstractEntityClass();
        $this->assertTrue($obj->has('foo'));

        $this->assertFalse($obj->has('bar'));
    }

    /**
     * Asserts that entity objects can be serialized / unserialized.
     */
    public function testSerializable()
    {
        $this->obj->set('foo', 'bar');

        $s = serialize($this->obj);
        $o = unserialize($s);

        $this->assertEquals($o->get('foo'), 'bar');
        $this->assertEquals($o, $this->obj);
    }

    /**
     * Asserts that entity objects can be json encoded.
     */
    public function testJsonSerializable()
    {
        $obj = $this->obj;
        $obj->set('foo', 'bar');
        $ret = json_encode($obj);
        $this->assertEquals(trim('{"foo":"bar"}'), $ret);

        $data = json_decode($ret, true);
        $this->assertEquals($data, $obj->data());
    }

    /**
     * Asserts that the data container can be passed keys in camelCase or pascal_case format
     * - setting in pascal_case can be retrieved  in camelCase.
     * - setting in camelCase can be retrieved in pascal_case.
     * - unsetting works in both camelCase and pascal_case.
     * - checking (with has() or isset) works in both cases.
     *
     * Basically asserts that both cases can be used interchangeably.
     */
    public function testCamelize()
    {
        $this->obj->set('foo_bar', 42);
        $this->assertEquals(42, $this->obj->get('fooBar'));
        $this->assertTrue(isset($this->obj['fooBar']));
        $this->assertTrue($this->obj->has('fooBar'));

        unset($this->obj['fooBar']);
        $this->assertFalse(isset($this->obj['foo_bar']));
        $this->assertFalse($this->obj->has('fooBar'));

        $this->obj['barBaz'] = 'x';
        $this->assertEquals('x', $this->obj['bar_baz']);
        $this->assertTrue(isset($this->obj['bar_baz']));
        $this->assertTrue($this->obj->has('bar_baz'));

        unset($this->obj['bar_baz']);
        $this->assertFalse(isset($this->obj['barBaz']));
        $this->assertFalse($this->obj->has('bar_baz'));
    }
}
