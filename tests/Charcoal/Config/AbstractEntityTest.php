<?php

namespace Charcoal\Tests\Config;

/**
 *
 */
class AbstractEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var mixed The Abstract Config mock
     */
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Config\AbstractEntity');
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

    /**
     * Asserts that the `set()` method:
     * - sets the value
     * and that the `get()` method:
     * - gets the value
     */
    public function testSetGet()
    {
        $obj = $this->obj;
        $this->assertNull($obj->get('foobar'));

        $obj->set('foobar', 42);
        $this->assertEquals(42, $obj->get('foobar'));
    }

    public function testSetGetWithSetterGetter()
    {
        $obj = new \Charcoal\Tests\Config\AbstractEntityClass();
        $obj->set('foo', 2);
        $this->assertEquals('foo is 12', $obj->get('foo'));
    }

    public function testHasWithSetterGetter()
    {
        $obj = new \Charcoal\Tests\Config\AbstractEntityClass();
        $this->assertTrue($obj->has('foo'));
    }

    /**
     * Asserts that:
     * - The `ArrayAccess` interface is properly implemented
     * - Setting by array sets the value properly
     * - Getting by array gets the value properly
     * - Unsetting by array unsets the key properly
     * - Using isset by arrat works properly
     */
    public function testArrayAccess()
    {
        $obj = $this->obj;
        $obj['foo'] = 'test';
        $this->assertEquals('test', $obj['foo']);

        $this->assertTrue(isset($obj['foo']));
        unset($obj['foo']);
        $this->assertNotTrue(isset($obj['foo']));
    }

    /**
     * Asserts that getting by array with a numeric index throws an exception.
     * Only string "keys" are valid.
     */
    public function testArrayAccessGetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj[0];
    }

    /**
     * Asserts that setting by array with a numeric index throws an exception.
     * Only string "keys" are valid.
     */
    public function testArrayAccessSetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj[0] = 'foo';
    }

    /**
     * Asserts that checking isset by array with a numeric index throws an exception.
     * Only string "keys" are valid.
     */
    public function testArrayAccessIssetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        isset($obj[0]);
    }

    /**
     * Asserts that checking isset by array with a numeric index throws an exception.
     * Only string "keys" are valid.
     */
    public function testArrayAccessUnsetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        unset($obj[0]);
    }

    /**
     * Asserts that the `setDelegates` method is chainable.
     */
    public function testSetDelegatesIsChainable()
    {
        $delegate = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig');
        $ret = $this->obj->setDelegates([$delegate]);
        $this->assertSame($ret, $this->obj);
    }

    /**
     * Asserts that the `addDelegate` method is chainable.
     */
    public function testAddDelegateIsChainable()
    {
        $delegate = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig');
        $ret = $this->obj->addDelegate($delegate);
        $this->assertSame($ret, $this->obj);
    }

    /**
     * Asserts that the `prependDelegate` method is chainable.
     */
    public function testprependDelegateIsChainable()
    {
        $delegate = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig');
        $ret = $this->obj->prependDelegate($delegate);
        $this->assertSame($ret, $this->obj);
    }

    /**
     * Asserts that
     * - The delegate is actually used when accessing a non-existing key.
     * - The order of the delegates are respected.
     */
    public function testDelegates()
    {
        $obj = $this->obj;

        $this->assertFalse($obj->has('foo'));

        $delegate = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig');
        $delegate->set('foo', 'bar');
        $obj->addDelegate($delegate);

        $this->assertTrue($obj->has('foo'));
        $this->assertEquals('bar', $obj->get('foo'));

        $delegate2 = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig');
        $delegate2->set('foo', 'baz');

        $obj->addDelegate($delegate2);
        $this->assertEquals('bar', $obj->get('foo'));

        $obj->prependDelegate($delegate2);
        $this->assertEquals('baz', $obj->get('foo'));
    }

    /**
     * Asserts that entity objects can be serialized / unserialized.
     */
    public function testSerializable()
    {
        $obj = $this->obj;
        $obj->set('foo', 'bar');

        $s = serialize($obj);
        $o = unserialize($s);

        $this->assertEquals($o->get('foo'), 'bar');
        $this->assertEquals($o, $obj);
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
    }

}
