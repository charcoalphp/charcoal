<?php

namespace Charcoal\Tests\Source;

use \Charcoal\Source\SourceConfig as SourceConfig;

class SourceConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultData()
    {
        $obj = new SourceConfig();
        $defaults = $obj->default_data();

        $this->assertEquals($obj->type(), $defaults['type']);
    }

    public function testSetType()
    {
        $obj = new SourceConfig();
        $ret = $obj->set_type('foo');

        $this->assertSame($obj, $ret);
        $this->assertEquals('foo', $obj->type());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_type([1,2,3]);
    }
}
