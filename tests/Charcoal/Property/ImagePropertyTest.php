<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\ImageProperty as ImageProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class ImagePropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $obj = new ImageProperty();
        $this->assertEquals('image', $obj->type());
    }
}
