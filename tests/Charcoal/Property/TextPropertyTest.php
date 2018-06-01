<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\TextProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class TextPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var TextProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new TextProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * Asserts that the `type()` method returns "text".
     *
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('text', $this->obj->type());
    }

    /**
     * Asserts that the `defaultMaxLength` method returns 0 (no limit).
     *
     * @return void
     */
    public function testDefaultMaxLength()
    {
        $this->assertEquals(0, $this->obj->defaultMaxLength());
    }

    /**
     * Asserts that the `sqlType()` method returns "TEXT".
     *
     * @return void
     */
    public function testSqlType()
    {
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }
}
