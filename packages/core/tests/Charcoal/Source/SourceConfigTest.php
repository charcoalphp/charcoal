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
    public function testDefaultData(): void
    {
        $obj = new SourceConfig();
        $defaults = $obj->defaults();

        $this->assertEquals($obj->type(), $defaults['type']);
    }

    public function testSetType(): void
    {
        $obj = new SourceConfig();
        $ret = $obj->setType('foo');

        $this->assertSame($obj, $ret);
        $this->assertEquals('foo', $obj->type());

        $this->expectException(InvalidArgumentException::class);
        $obj->setType([ 1, 2, 3 ]);
    }
}
