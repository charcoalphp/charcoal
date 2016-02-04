<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\LangProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class LangPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new LangProperty();
    }

    public function testType()
    {
        $obj = $this->obj;
        $this->assertEquals('lang', $obj->type());
    }
}
