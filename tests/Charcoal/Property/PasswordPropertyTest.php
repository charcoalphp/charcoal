<?php

namespace Charcoal\Tests\Property;

use \PDO;

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
            'database' => new PDO('sqlite::memory:'),
            'logger' => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
        $this->assertEquals('password', $obj->type());
    }
}
