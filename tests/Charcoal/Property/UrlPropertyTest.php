<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\UrlProperty;

/**
 *
 */
class UrlPropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new UrlProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * Asserts that the `type()` method returns "url".
     */
    public function testType()
    {
        $this->assertEquals('url', $this->obj->type());
    }
}
