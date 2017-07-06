<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\ModelStructureProperty;

/**
 *
 */
class ModelStructurePropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var ModelStructureProperty $obj
     */
    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new ModelStructureProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('model-structure', $this->obj->type());
    }
}
