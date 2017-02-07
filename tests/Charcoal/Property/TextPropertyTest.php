<?php

namespace Charcoal\Tests\Property;

use PDO;

use Psr\Log\NullLogger;

use Charcoal\Property\TextProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class TextPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new TextProperty([
            'database'  => new PDO('sqlite::memory:'),
            'logger'    => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    /**
     * Asserts that the `type()` method returns "text".
     */
    public function testType()
    {
        $this->assertEquals('text', $this->obj->type());
    }

    /**
     * Asserts that the `defaultMaxLength` method returns 0 (no limit).
     */
    public function testDefaultMaxLength()
    {
        $this->assertEquals(0, $this->obj->defaultMaxLength());
    }

    /**
     * Asserts that the `sqlType()` method returns "TEXT".
     */
    public function testSqlType()
    {
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }
}
