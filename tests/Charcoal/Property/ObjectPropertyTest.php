<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\ObjectProperty as ObjectProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class ObjectPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $obj = new ObjectProperty();
        $this->assertEquals('object', $obj->type());
    }
}
