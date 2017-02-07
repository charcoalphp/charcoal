<?php

namespace Charcoal\Tests\Property;

use \PDO;

use \Psr\Log\NullLogger;

use \Charcoal\Property\PhoneProperty;

/**
 *
 */
class PhonePropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhoneProperty $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $this->obj = new PhoneProperty([
            'database' => new PDO('sqlite::memory:'),
            'logger' => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    /**
     * Hello world
     */
    public function testDefaultValues()
    {
        $this->assertInstanceOf('\Charcoal\Property\PhoneProperty', $this->obj);

        $this->assertEquals(0, $this->obj->minLength());
        $this->assertEquals(16, $this->obj->maxLength());
    }

    public function testType()
    {
        $this->assertEquals('phone', $this->obj->type());
    }

    public function testSanitize()
    {
        $this->assertEquals('5145551234', $this->obj->sanitize('(514) 555-1234'));
    }

    public function testDisplayVal()
    {
        $this->assertEquals('(514) 555-1234', $this->obj->displayVal('5145551234'));

        $this->assertEquals('(514) 555-1234', $this->obj->displayVal('514-555-1234'));
    }
}
