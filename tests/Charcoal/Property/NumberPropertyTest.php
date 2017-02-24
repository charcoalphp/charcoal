<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\NumberProperty;

/**
 *
 */
class NumberPropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var NumberProperty $obj
     */
    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new NumberProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType()
    {
        $obj = $this->obj;
        $this->assertEquals('number', $obj->type());
    }
}
