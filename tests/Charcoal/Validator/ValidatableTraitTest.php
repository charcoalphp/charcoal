<?php

namespace Charcoal\Tests\Validator;

// From 'charcoal-core'
use Charcoal\Tests\Mock\ValidatableClass;

/**
 *
 */
class ValidatableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new ValidatableClass();
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf(ValidatableClass::class, $obj);
    }
}
