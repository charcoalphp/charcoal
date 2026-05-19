<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSourceConfig;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class DatabaseSourceConfigTest extends AbstractTestCase
{
    public function testDefaultData(): void
    {
        $obj = new DatabaseSourceConfig();
        $defaults = $obj->defaults();

        $this->assertEquals('mysql', $obj->type());
        $this->assertEquals($obj->type(), $defaults['type']);
        $this->assertEquals($obj->hostname(), $defaults['hostname']);
    }

    public function testMerge(): void
    {
        $obj = new DatabaseSourceConfig();
        $ret = $obj->merge([]);
        $this->assertSame($ret, $obj);
    }

    public function testSetHostname(): void
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals('localhost', $obj->hostname());
        $ret = $obj->setHostname('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->hostname());

        $this->expectException(InvalidArgumentException::class);
        $obj->setHostname(false);
    }

    public function testSetUsername(): void
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(null, $obj->username());
        $ret = $obj->setUsername('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->username());

        $this->expectException(InvalidArgumentException::class);
        $obj->setUsername(false);
    }

    public function testSetPassword(): void
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals('', $obj->password());
        $ret = $obj->setPassword('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->password());

        $this->expectException(InvalidArgumentException::class);
        $obj->setPassword(false);
    }

    public function testSetDatabase(): void
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(null, $obj->database());
        $ret = $obj->setDatabase('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->database());

        $this->expectException(InvalidArgumentException::class);
        $obj->setDatabase(false);
    }

    public function testSetDisableUtf8(): void
    {
        $obj = new DatabaseSourceConfig();
        $this->assertEquals(false, $obj->disableUtf8());
        $ret = $obj->setDisableUtf8(true);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->disableUtf8());
    }
}
