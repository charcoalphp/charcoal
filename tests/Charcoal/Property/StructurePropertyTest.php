<?php

namespace Charcoal\Tests\Property;

use \Psr\Log\NullLogger;

use \Charcoal\Property\StructureProperty;

/**
 *
 */
class StructurePropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new StructureProperty([
            'logger' => new NullLogger()
        ]);
    }

    public function testType()
    {
        $obj = new StructureProperty([
            'logger' => new NullLogger()
        ]);
        $this->assertEquals('structure', $obj->type());
    }
}
