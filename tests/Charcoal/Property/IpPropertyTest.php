<?php

namespace Charcoal\Tests\Property;

use \PDO;

use \Psr\Log\NullLogger;

use \Charcoal\Property\IpProperty;

/**
 *
 */
class IpPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new IpProperty([
            'database' => new PDO('sqlite::memory:'),
            'logger' => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('ip', $this->obj->type());
    }

    public function testDefaults()
    {
        $this->assertEquals('string', $this->obj->storageMode());
    }

    public function testMultipleCannotBeTrue()
    {
        $this->assertFalse($this->obj->multiple());

        $this->assertSame($this->obj, $this->obj->setMultiple(false));
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMultiple(true);
    }

    public function testL10nCannotBeTrue()
    {
        $this->assertFalse($this->obj->l10n());

        $this->assertSame($this->obj, $this->obj->setL10n(false));
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setL10n(true);
    }

    public function testSetStorageMode()
    {
        $this->assertEquals('string', $this->obj->storageMode());
        $ret = $this->obj->setStorageMode('int');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('int', $this->obj->storageMode());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setStorageMode('foobar');
    }

    public function testIntVal()
    {
        $this->assertEquals(0, $this->obj->intVal('0.0.0.0'));
        $this->assertEquals(2130706433, $this->obj->intVal('127.0.0.1'));
        $this->assertEquals(3232235777, $this->obj->intVal('192.168.1.1'));
        $this->assertEquals(3232235777, $this->obj->intVal(3232235777));
        $this->assertEquals(3232235777, $this->obj->intVal('3232235777'));
    }

    public function testStringVal()
    {
        $this->assertEquals('0.0.0.0', $this->obj->stringVal(0));
        $this->assertEquals('127.0.0.1', $this->obj->stringVal(2130706433));
        $this->assertEquals('192.168.1.1', $this->obj->stringVal(3232235777));
        $this->assertEquals('8.8.8.8', $this->obj->stringVal('8.8.8.8'));
    }

    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    /**
     * Asserts that the `sqlType()` method:
     * - returns "VARCHAR(15)" if the storage mode is "string" (default).
     * - returns "BIGINT" if the storage mode is "int".
     */
    public function testSqlType()
    {
        $this->obj->setStorageMode('string');
        $this->assertEquals('VARCHAR(15)', $this->obj->sqlType());

        $this->obj->setStorageMode('int');
        $this->assertEquals('BIGINT', $this->obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $this->obj->setStorageMode('string');
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());

        $this->obj->setStorageMode('int');
        $this->assertEquals(PDO::PARAM_INT, $this->obj->sqlPdoType());
    }
}
