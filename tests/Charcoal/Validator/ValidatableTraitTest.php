<?php

namespace Charcoal\Tests\Validator;

class ValidatableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public static function setUpBeforeClass()
    {
        include_once 'ValidatableClass.php';
    }

    public function setUp()
    {
        $this->obj = new ValidatableClass();
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Tests\Validator\ValidatableClass', $obj);
    }
}
