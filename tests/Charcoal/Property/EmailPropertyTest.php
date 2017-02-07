<?php

namespace Charcoal\Tests\Property;

use \PDO;

use \Psr\Log\NullLogger;

use \Charcoal\Property\EmailProperty;

/**
 *
 */
class EmailPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new EmailProperty([
            'database'  => new PDO('sqlite::memory:'),
            'logger'    => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    /**
     * Asserts that the `type()` method returns "url".
     */
    public function testType()
    {
        $this->assertEquals('email', $this->obj->type());
    }

    public function testMaxLength()
    {
        $this->assertEquals(254, $this->obj->maxLength());

        $this->obj->setMaxLength(100);
        $this->assertEquals(254, $this->obj->maxLength());
    }

    public function testValidateEmail()
    {
        $this->obj->setVal('foo@example.com');
        $this->assertTrue($this->obj->validateEmail());
        $this->obj->setVal('foo.bar@example.museum');
        $this->assertTrue($this->obj->validateEmail());

        $this->obj->setVal(42);
        $this->assertFalse($this->obj->validateEmail());
        $this->obj->setVal(false);
        $this->assertFalse($this->obj->validateEmail());
        $this->obj->setVal('foo');
        $this->assertFalse($this->obj->validateEmail());
         $this->obj->setVal('foo@');
        $this->assertFalse($this->obj->validateEmail());
    }
}
