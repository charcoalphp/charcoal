<?php

namespace Charcoal\Tests\Validator;

use \Charcoal\Validator\ValidatorResult as ValidatorResult;

class ValidatorResultTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $obj = new ValidatorResult();
        $this->assertInstanceOf('\Charcoal\Validator\ValidatorResult', $obj);
    }
}
