<?php

namespace Charcoal\Tests\Validator;


class AbstractValidatorTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    static public function setUpBeforeClass()
    {
        include_once 'AbstractValidatorClass.php';
        include_once 'ValidatableClass.php';
    }

    public function setUp()
    {
        $this->model = new ValidatableClass();
        $this->obj = new AbstractValidatorClass($this->model);
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Validator\AbstractValidator', $obj);
    }
}
