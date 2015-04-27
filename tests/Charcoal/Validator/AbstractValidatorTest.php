<?php

namespace Charcoal\Tests\Validator;

use \Charcoal\Validator\ValidatorResult as ValidatorResult;

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

    public function testError()
    {
        $obj = $this->obj;
        $ret = $obj->error('foo');
        $this->assertSame($ret, $obj);
        //var_dump($obj->error_results());
    }

    public function testWarning()
    {
        $obj = $this->obj;
        $ret = $obj->warning('foo');
        $this->assertSame($ret, $obj);
        //var_dump($obj->warning_results());
    }

    public function testNotice()
    {
        $obj = $this->obj;
        $ret = $obj->notice('foo');
        $this->assertSame($ret, $obj);
        //var_dump($obj->notice_results());
    }

    public function testAddResult()
    {
        $result = [
            'ident'=>'bar',
            'level'=>AbstractValidatorClass::ERROR,
            'message'=>'foo'
        ];

        $obj = $this->obj;
        $ret = $obj->add_result($result);
        $this->assertSame($ret, $obj);

        $result_obj = new ValidatorResult($result);
        $ret = $obj->add_result($result_obj);
        $this->assertSame($ret, $obj);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_result(false);
    }
}
