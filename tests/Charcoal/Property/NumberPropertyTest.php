<?php

namespace Charcoal\Tests\Property;

use PDO;

use Psr\Log\NullLogger;

use Charcoal\Property\NumberProperty;

/**
 *
 */
class NumberPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NumberProperty $obj
     */
    public $obj;

    public function setUp()
    {
        $this->obj = new NumberProperty([
            'database'  => new PDO('sqlite::memory:'),
            'logger'    => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    public function testType()
    {
        $obj = $this->obj;
        $this->assertEquals('number', $obj->type());
    }
}
