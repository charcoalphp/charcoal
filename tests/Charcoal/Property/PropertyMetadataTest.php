<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\PropertyMetadata;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class PropertyMetadataTest extends AbstractTestCase
{
    /**
     * @var PropertyMetadata
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->obj = new PropertyMetadata();
    }

    /**
     * @return void
     */
    public function testSetIdent()
    {
        $this->assertEquals('', $this->obj->ident());
        $ret = $this->obj->setIdent('foo');
        $this->assertSame($ret, $this->obj);

        $this->assertEquals('foo', $this->obj->ident());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setIdent(false);
    }
}
