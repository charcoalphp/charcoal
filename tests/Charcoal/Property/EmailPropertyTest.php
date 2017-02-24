<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\EmailProperty;

/**
 *
 */
class EmailPropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new EmailProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
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
