<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\StructureProperty;

/**
 *
 */
class StructurePropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new StructureProperty();
    }

    public function testType()
    {
        $obj = new StructureProperty();
        $this->assertEquals('structure', $obj->type());
    }
}
