<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\MapStructureProperty;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MapStructureProperty::class)]
class MapStructurePropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var MapStructureProperty
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = new MapStructureProperty([
            'database'   => $container->get('database'),
            'logger'     => $container->get('logger'),
            'translator' => $container->get('translator')
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('map-structure', $this->obj->type());
    }
}
