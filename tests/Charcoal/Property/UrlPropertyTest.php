<?php

namespace Charcoal\Tests\Property;

use PDO;

use Psr\Log\NullLogger;

use Charcoal\Property\UrlProperty;

/**
 *
 */
class UrlPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new UrlProperty([
            'database'  => new PDO('sqlite::memory:'),
            'logger'    => new NullLogger(),
            'translator' => $GLOBALS['translator']
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
