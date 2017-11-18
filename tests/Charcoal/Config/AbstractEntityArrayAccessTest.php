<?php

namespace Charcoal\Tests\Config;

use PHPUnit_Framework_TestCase;

use Charcoal\Tests\Config\AbstractEntityClass;

use InvalidArgumentException;

use Charcoal\Config\AbstractEntity;

/**
 *
 */
class AbstractEntityArrayAccessTest extends PHPUnit_Framework_TestCase
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

    public function testArrayAccessGetUnknownKeyReturnsNull()
    {
        $this->assertNull($this->obj['foobarbaz']);
    }

    /**
     * Asserts that:
     * - The `ArrayAccess` interface is properly implemented
     * - Setting by array sets the value properly
     * - Getting by array gets the value properly
     * - Unsetting by array unsets the key properly
     * - Using isset by array works properly
     */
    public function testArrayAccessSetAndGet()
    {
        $this->obj['foo'] = 'test';
        $this->assertEquals('test', $this->obj['foo']);

        $this->obj['foo'] = 'xxx';
        $this->assertEquals('xxx', $this->obj['foo']);
    }

    public function testArrayAccessGetEmpty()
    {
        $this->assertNull($this->obj['']);
    }

    public function testArrayAccessSetEmpty()
    {
        $this->obj[''] = 'foo';
        $this->assertNull($this->obj['']);
    }

    public function testArrayAccessGetUnderscore()
    {
        $this->assertNull($this->obj['_']);
    }

    public function testArrayAccessSetUnderscore()
    {
        $this->obj['_'] = 'foo';
        $this->assertNull($this->obj['_']);
    }

    public function testArrayAccessIssetUnset()
    {
        $this->assertFalse(isset($this->obj['foo']));
        $this->obj['foo'] = 42;

        $this->assertTrue(isset($this->obj['foo']));

        unset($this->obj['foo']);
        $this->assertFalse(isset($this->obj['foo']));
    }

    /**
     * Asserts that getting by array with a numeric index throws an exception.
     * Only string "keys" are valid.
     */
    public function testArrayAccessGetNumericException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj[0];
    }

    /**
     * Asserts that setting by array with a numeric index throws an exception.
     * Only string "keys" are valid.
     */
    public function testArrayAccessSetNumericException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj[0] = 'foo';
    }

    /**
     * Asserts that checking isset by array with a numeric index throws an exception.
     * Only string "keys" are valid.
     */
    public function testArrayAccessIssetNumericException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        isset($this->obj[0]);
    }

    /**
     * Asserts that checking isset by array with a numeric index throws an exception.
     * Only string "keys" are valid.
     */
    public function testArrayAccessUnsetNumericException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        unset($this->obj[0]);
    }
}
