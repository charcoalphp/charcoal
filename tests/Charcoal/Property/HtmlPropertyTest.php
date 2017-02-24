<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\HtmlProperty;

/**
 *
 */
class HtmlPropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new HtmlProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType()
    {
        $obj = $this->obj;
        $this->assertEquals('html', $obj->type());
    }

    public function testDefaultMaxLength()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->maxLength());

        $this->assertEquals(0, $obj->defaultMaxLength());
    }

    public function testSqlType()
    {
        $obj = $this->obj;
        $this->assertEquals('TEXT', $obj->sqlType());
    }
}
