<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\EmailProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class EmailPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var EmailProperty
     */
    public $obj;

    /**
     * @return void
     */
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
     *
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('email', $this->obj->type());
    }

    /**
     * @return void
     */
    public function testMaxLength()
    {
        $this->assertEquals(254, $this->obj['maxLength']);

        $this->obj->setMaxLength(100);
        $this->assertEquals(254, $this->obj['maxLength']);
    }

    /**
     * @return void
     */
    public function testValidateEmail()
    {
        $this->obj['allowNull'] = false;
        $this->obj['required'] = true;

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

    /**
     * @return void
     */
    public function testValidationMethods()
    {
        $this->assertContains('email', $this->obj->validationMethods());
    }

    public function testParseVal()
    {
        $this->assertEquals('charcoal@example.com', $this->obj->parseVal('charcoal@example.com'));
    }

    public function testDisplayVal()
    {
        $this->assertEquals('charcoal@example.com', $this->obj->displayVal('charcoal@example.com'));
    }

    public function testInputVal()
    {
        $this->assertEquals('charcoal@example.com', $this->obj->inputVal('charcoal@example.com'));
    }

    public function testStorageVal()
    {
        $this->assertEquals('charcoal@example.com', $this->obj->storageVal('charcoal@example.com'));
    }
}
