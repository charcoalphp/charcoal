<?php

namespace Charcoal\Tests\Source;

use \Charcoal\Tests\Source\StorableClass;

include __DIR__.'/StorableClass.php';

class StorableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new StorableClass();
    }

    /**
     * Asserts that the `setId()` method:
     * - is chainable
     * - sets the id properly
     * - trhows
     */
    public function testSetId()
    {
        $ret = $this->obj->setId('allo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('allo', $this->obj->id());

        $this->assertEquals(42, $this->obj->setId(42)->id());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setId(null);
    }

    /**
     * Asserts that the `key()` method:
     * - defaults to "id"
     * and that the `setKey()` method:
     * - is chainable
     * - sets the key
     * - throws an exception if the key is not a string
     */
    public function testSetKey()
    {
        $this->assertEquals('id', $this->obj->key());
        $ret = $this->obj->setKey('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->key());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setKey(false);
    }

    /**
     * @dataProvider providerInvalidKey
     */
    public function testSetKeyNotAlphanumThrowsException($invalidKey)
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setKey($invalidKey);
    }

    public function providerInvalidKey()
    {
        return [
            ['Can not contain a space'],
            ['mémé'],
            ['\''],
            ['"test"'],
            ['-'],
            ['dash-dash'],
            ['%'],
            ['#not'],
            ['^']
        ];
    }
}
