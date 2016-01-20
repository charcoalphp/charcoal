<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\TextProperty as TextProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class TextPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $obj = new TextProperty();
        $this->assertEquals('text', $obj->type());
    }
}
