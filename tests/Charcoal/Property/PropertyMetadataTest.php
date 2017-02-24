<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\PropertyMetadata;

/**
 *
 */
class PropertyMetadataTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new PropertyMetadata();
    }

    public function testSetIdent()
    {
        $this->assertEquals('', $this->obj->ident());
        $ret = $this->obj->setIdent('foo');
        $this->assertSame($ret, $this->obj);

        $this->assertEquals('foo', $this->obj->ident());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setIdent(false);
    }
}
