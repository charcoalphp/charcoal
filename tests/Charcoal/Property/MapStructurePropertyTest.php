<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\MapStructureProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class MapStructurePropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var MapStructureProperty $obj
     */
    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new MapStructureProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType()
    {
        $obj = $this->obj;
        $this->assertEquals('map-structure', $obj->type());
    }
}
