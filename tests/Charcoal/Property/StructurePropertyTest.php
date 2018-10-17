<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\StructureProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class StructurePropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var StructureProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new StructureProperty([
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
        $this->assertEquals('structure', $this->obj->type());
    }

    public function testSetL10nThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setL10n(true);
    }
}
