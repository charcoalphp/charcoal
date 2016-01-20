<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\PasswordProperty as PasswordProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class PasswordPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $obj = new PasswordProperty();
        $this->assertEquals('password', $obj->type());
    }
}
