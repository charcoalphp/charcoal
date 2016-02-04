<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\PropertyField;

/**
 *
 */
class PropertyFieldTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new PropertyField();
    }

    public function testSetIdent()
    {
        $obj = $this->obj;

        $ret = $obj->setIdent('foobar');
        $this->assertSame($obj, $ret);

        $this->assertEquals('foobar', $obj->ident());
    }

    public function testSqlReturnsEmptyIfEmptyIdent()
    {
        $obj = $this->obj;
        $obj->setIdent('');
        $this->assertEquals('', $obj->sql());
    }
}
