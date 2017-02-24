<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\PropertyField;

/**
 *
 */
class PropertyFieldTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new PropertyField([
            'logger'     => $container['logger'],
            'translator' => $container['translator']
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
