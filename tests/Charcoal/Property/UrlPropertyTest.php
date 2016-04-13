<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\UrlProperty;

/**
 *
 */
class UrlPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new UrlProperty();
    }

    /**
     * Asserts that the `type()` method returns "url".
     */
    public function testType()
    {
        $this->assertEquals('url', $this->obj->type());
    }
}
