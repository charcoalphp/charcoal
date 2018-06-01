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

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new NumberProperty([
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
        $this->assertEquals('number', $this->obj->type());
    }
}
