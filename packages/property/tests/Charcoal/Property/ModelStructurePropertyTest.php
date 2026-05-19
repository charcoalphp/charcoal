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

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = new ModelStructureProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType(): void
    {
        $this->assertEquals('model-structure', $this->obj->type());
    }

    public function testSetStructureMetadata(): void
    {
        $ret = $this->obj->setStructureMetadata(null);
        $this->assertSame($ret, $this->obj);

        $this->obj->setStructureMetadata([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setStructureMetadata('foo');
    }

    public function setStructureInterfaces(): void
    {
        $ret = $this->obj->setStructureInterfaces([]);
        $this->assertSame($ret, $this->obj);
    }
}
