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

    public function testResults()
    {
        $result = [
            'ident'=>'bar',
            'level'=>AbstractValidatorClass::ERROR,
            'message'=>'foo'
        ];

        $obj = $this->obj;
        $this->assertEquals([], $obj->results());

        $obj->add_result($result);
        $result_obj = new ValidatorResult($result);
        $this->assertEquals([AbstractValidatorClass::ERROR=>[$result_obj]], $obj->results());
    }

    public function testErrorResults()
    {
        $result = [
            'ident'=>'bar',
            'level'=>AbstractValidatorClass::ERROR,
            'message'=>'foo'
        ];
        $result2 = [
            'ident'=>'foo',
            'level'=>AbstractValidatorClass::NOTICE,
            'message'=>'bar'
        ];
        $obj = $this->obj;
        $this->assertEquals([], $obj->error_results());

        $obj->add_result($result);
        $obj->add_result($result2);
        $result_obj = new ValidatorResult($result);
        $this->assertEquals([$result_obj], $obj->error_results());
    }

    public function testWarningResults()
    {
        $result = [
            'ident'=>'bar',
            'level'=>AbstractValidatorClass::WARNING,
            'message'=>'foo'
        ];
        $result2 = [
            'ident'=>'foo',
            'level'=>AbstractValidatorClass::NOTICE,
            'message'=>'bar'
        ];
        $obj = $this->obj;
        $this->assertEquals([], $obj->warning_results());

        $obj->add_result($result);
        $obj->add_result($result2);
        $result_obj = new ValidatorResult($result);
        $this->assertEquals([$result_obj], $obj->warning_results());
    }

    public function testNoticeResults()
    {
        $result = [
            'ident'=>'bar',
            'level'=>AbstractValidatorClass::NOTICE,
            'message'=>'foo'
        ];
        $result2 = [
            'ident'=>'foo',
            'level'=>AbstractValidatorClass::ERROR,
            'message'=>'bar'
        ];
        $obj = $this->obj;
        $this->assertEquals([], $obj->notice_results());

        $obj->add_result($result);
        $obj->add_result($result2);
        $result_obj = new ValidatorResult($result);
        $this->assertEquals([$result_obj], $obj->notice_results());
    }

    public function testMerge()
    {
        $result = [
            'ident'=>'bar',
            'level'=>AbstractValidatorClass::NOTICE,
            'message'=>'foo'
        ];
        $result2 = [
            'ident'=>'foo',
            'level'=>AbstractValidatorClass::ERROR,
            'message'=>'bar'
        ];
        $result_obj = new ValidatorResult($result);
        $result2_obj = new ValidatorResult($result2);
        $obj = $this->obj;
        $obj2 = new AbstractValidatorClass($this->model);

        $obj->add_result($result);
        $obj2->add_result($result2);
        $obj->merge($obj2);

        $this->assertEquals([
            AbstractValidatorClass::NOTICE=>[$result_obj],
            AbstractValidatorClass::ERROR=>[$result2_obj]
        ], $obj->results());

    }
}
