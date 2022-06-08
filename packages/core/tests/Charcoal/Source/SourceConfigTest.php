<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\SourceConfig;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class SourceConfigTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testDefaultData()
    {
        $obj = new SourceConfig();
        $defaults = $obj->defaults();

        $this->assertEquals($obj->type(), $defaults['type']);
    }

    /**
     * @return void
     */
    public function testSetType()
    {
        $obj = new SourceConfig();
        $ret = $obj->setType('foo');

        $this->assertSame($obj, $ret);
        $this->assertEquals('foo', $obj->type());

        $this->expectException(InvalidArgumentException::class);
        $obj->setType([ 1, 2, 3 ]);
    }
}
