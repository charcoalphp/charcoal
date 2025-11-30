<?php

namespace Charcoal\Tests\Validator;

// From 'charcoal-core'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Mock\ValidatableClass;
use Charcoal\Validator\ValidatableTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ValidatableTrait::class)]
class ValidatableTraitTest extends AbstractTestCase
{
    /**
     * @var ValidatableClass
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = new ValidatableClass();
    }

    /**
     * @return void
     */
    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf(ValidatableClass::class, $obj);
    }
}
