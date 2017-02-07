<?php

namespace Charcoal\Tests\Property;

use \Psr\Log\NullLogger;

use \Charcoal\Property\PropertyField;

/**
 *
 */
class PropertyFieldTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new PropertyField([
            'logger' => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($this->obj, $ret);

        $this->assertEquals('foobar', $this->obj->ident());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setIdent(null);
    }

    public function testSqlReturnsEmptyIfEmptyIdent()
    {
        $this->obj->setIdent('');
        $this->assertEquals('', $this->obj->sql());
    }

    public function testDefaultLabelIsNull()
    {
        $this->assertNull($this->obj->label());
    }

    public function testSetLabel()
    {
        $ret = $this->obj->setLabel('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', (string)$this->obj->label());
    }

    public function testSetSqlType()
    {
        $ret = $this->obj->setSqlType('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->sqlType());
    }
}
