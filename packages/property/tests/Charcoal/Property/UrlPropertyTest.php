<?php

declare(strict_types=1);

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

    protected function setUp(): void
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
    public function testType(): void
    {
        $this->assertEquals('url', $this->obj->type());
    }

    public function testParseOne(): void
    {
        $this->assertEquals('example.com', $this->obj->parseOne('example.com'));
        $this->assertEquals('https://example.com:2020', $this->obj->parseOne('<script></script>https:// example.com:2020 '));
    }
}
