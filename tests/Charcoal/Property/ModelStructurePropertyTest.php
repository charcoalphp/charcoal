<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\ModelStructureProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ModelStructurePropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var ModelStructureProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new ModelStructureProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('model-structure', $this->obj->type());
    }
}
