<?php

namespace Charcoal\Tests\Validator;

// From 'charcoal-core'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Mock\ValidatableClass;

/**
 *
 */
class ValidatableTraitTest extends AbstractTestCase
{
    /**
     * @var ValidatableClass
     */
    public $obj;

    protected function setUp(): void
    {
        $this->obj = new ValidatableClass();
    }

    public function testConstructor(): void
    {
        $obj = $this->obj;
        $this->assertInstanceOf(ValidatableClass::class, $obj);
    }
}
