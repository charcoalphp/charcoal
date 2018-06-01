<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\UrlProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class UrlPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var UrlProperty
     */
    public $obj;

    /**
     * @return void
     */
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
     *
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('url', $this->obj->type());
    }
}
