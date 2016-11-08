<?php

namespace Charcoal\Tests\Property;

use \Psr\Log\NullLogger;

use \Charcoal\Property\UrlProperty;

/**
 *
 */
class UrlPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new UrlProperty([
            'logger' => new NullLogger()
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
