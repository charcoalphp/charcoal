<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\HtmlProperty as HtmlProperty;

/**
 *
 */
class HtmlPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $obj = new HtmlProperty();
        $this->assertEquals('html', $obj->type());
    }
}
