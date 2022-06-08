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

    public function testParseOne()
    {
        $this->assertEquals('example.com', $this->obj->parseOne('example.com'));
        $this->assertEquals('https://example.com:2020', $this->obj->parseOne('<script></script>https:// example.com:2020 '));
    }
}
