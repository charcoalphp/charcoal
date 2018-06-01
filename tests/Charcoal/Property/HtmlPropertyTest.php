<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\HtmlProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class HtmlPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var HtmlProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new HtmlProperty([
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
        $obj = $this->obj;
        $this->assertEquals('html', $obj->type());
    }

    /**
     * @return void
     */
    public function testDefaultMaxLength()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->maxLength());

        $this->assertEquals(0, $obj->defaultMaxLength());
    }

    /**
     * @return void
     */
    public function testSqlType()
    {
        $obj = $this->obj;
        $this->assertEquals('TEXT', $obj->sqlType());
    }
}
