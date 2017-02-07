<?php

namespace Charcoal\Tests\Property;

use \PDO;

use \Psr\Log\NullLogger;

use \Charcoal\Property\HtmlProperty;

/**
 *
 */
class HtmlPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new HtmlProperty([
            'database' => new PDO('sqlite::memory:'),
            'logger' => new NullLogger(),
            'translator' => $GLOBALS['translator']
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
