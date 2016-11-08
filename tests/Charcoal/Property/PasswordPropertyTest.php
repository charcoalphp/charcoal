<?php

namespace Charcoal\Tests\Property;

use \Psr\Log\NullLogger;

use \Charcoal\Property\PasswordProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class PasswordPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testType()
    {
        $obj = new PasswordProperty([
            'logger' => new NullLogger()
        ]);
        $this->assertEquals('password', $obj->type());
    }
}
