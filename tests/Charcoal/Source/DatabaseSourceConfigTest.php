<?php

namespace Charcoal\Tests\Source;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSourceConfig;

/**
 *
 */
class DatabaseSourceConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultData()
    {
        $obj = new DatabaseSourceConfig();
        $defaults = $obj->defaults();

        $this->assertEquals('mysql', $obj->type());
        $this->assertEquals($obj->type(), $defaults['type']);
        $this->assertEquals($obj->hostname(), $defaults['hostname']);
    }

    public function testMerge()
    {
        $obj = new DatabaseSourceConfig();
        $ret = $obj->merge([]);
        $this->assertSame($ret, $obj);
    }

    public function testSetHostname()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals('localhost', $obj->hostname());
        $ret = $obj->setHostname('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->hostname());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setHostname(false);
    }

    public function testSetUsername()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(null, $obj->username());
        $ret = $obj->setUsername('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->username());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setUsername(false);
    }

    public function testSetPassword()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals('', $obj->password());
        $ret = $obj->setPassword('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->password());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setPassword(false);
    }

    public function testSetDatabase()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(null, $obj->database());
        $ret = $obj->setDatabase('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->database());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setDatabase(false);
    }

    public function testSetDisableUtf8()
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(false, $obj->disableUtf8());
        $ret = $obj->setDisableUtf8(true);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->disableUtf8());
    }
}
