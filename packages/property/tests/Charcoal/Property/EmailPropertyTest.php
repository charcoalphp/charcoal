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

    protected function setUp(): void
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
    public function testType(): void
    {
        $this->assertEquals('email', $this->obj->type());
    }

    public function testMaxLength(): void
    {
        $this->assertEquals(254, $this->obj['maxLength']);

        $this->obj->setMaxLength(100);
        $this->assertEquals(254, $this->obj['maxLength']);
    }

    public function testValidateEmail(): void
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

    public function testValidationMethods(): void
    {
        $this->assertContains('email', $this->obj->validationMethods());
    }

    public function testParseVal(): void
    {
        $this->assertEquals('charcoal@example.com', $this->obj->parseVal('charcoal@example.com'));
    }

    public function testDisplayVal(): void
    {
        $this->assertEquals('charcoal@example.com', $this->obj->displayVal('charcoal@example.com'));
    }

    public function testInputVal(): void
    {
        $this->assertEquals('charcoal@example.com', $this->obj->inputVal('charcoal@example.com'));
    }

    public function testStorageVal(): void
    {
        $this->assertEquals('charcoal@example.com', $this->obj->storageVal('charcoal@example.com'));
    }
}
