<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\NumberProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class NumberPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var NumberProperty
     */
    public $obj;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = new NumberProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator'],
        ]);
    }

    public function testType(): void
    {
        $this->assertEquals('number', $this->obj->type());
    }

    public function testDefaults(): void
    {
        $this->assertNull($this->obj->getMin());
        $this->assertNull($this->obj->getMax());
    }

    public function testSetData(): void
    {
        $this->obj->setData([
            'min' => 0,
            'max' => 100,
        ]);
        $this->assertEquals(0, $this->obj->getMin());
        $this->assertEquals(100, $this->obj->getMax());
    }

    public function testValidationMethods(): void
    {
        $ret = $this->obj->validationMethods();
        $this->assertContains('min', $ret);
        $this->assertContains('max', $ret);
    }
}
