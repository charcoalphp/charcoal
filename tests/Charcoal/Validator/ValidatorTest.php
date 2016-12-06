<?php

namespace Charcoal\Tests\Validator;

use \Charcoal\Validator\ValidatorResult as ValidatorResult;

use \Charcoal\Tests\Mock\ValidatorClass;
use \Charcoal\Tests\Mock\ValidatableClass;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->model = new ValidatableClass();
        $this->obj   = new ValidatorClass($this->model);
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
        // var_dump($obj->errorResults());
    }

    public function testWarning()
    {
        $obj = $this->obj;
        $ret = $obj->warning('foo');
        $this->assertSame($ret, $obj);
        // var_dump($obj->warningResults());
    }

    public function testNotice()
    {
        $obj = $this->obj;
        $ret = $obj->notice('foo');
        $this->assertSame($ret, $obj);
        // var_dump($obj->noticeResults());
    }

    public function testAddResult()
    {
        $result = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::ERROR,
            'message' => 'foo'
        ];

        $obj = $this->obj;
        $ret = $obj->addResult($result);
        $this->assertSame($ret, $obj);

        $result_obj = new ValidatorResult($result);
        $ret = $obj->addResult($result_obj);
        $this->assertSame($ret, $obj);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->addResult(false);
    }

    public function testResults()
    {
        $result = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::ERROR,
            'message' => 'foo'
        ];

        $obj = $this->obj;
        $this->assertEquals([], $obj->results());

        $obj->addResult($result);
        $result_obj = new ValidatorResult($result);
        $this->assertEquals([ValidatorClass::ERROR => [$result_obj]], $obj->results());
    }

    public function testErrorResults()
    {
        $result = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::ERROR,
            'message' => 'foo'
        ];
        $result2 = [
            'ident'   => 'foo',
            'level'   => ValidatorClass::NOTICE,
            'message' => 'bar'
        ];
        $obj = $this->obj;
        $this->assertEquals([], $obj->errorResults());

        $obj->addResult($result);
        $obj->addResult($result2);
        $result_obj = new ValidatorResult($result);
        $this->assertEquals([$result_obj], $obj->errorResults());
    }

    public function testWarningResults()
    {
        $result = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::WARNING,
            'message' => 'foo'
        ];
        $result2 = [
            'ident'   => 'foo',
            'level'   => ValidatorClass::NOTICE,
            'message' => 'bar'
        ];
        $obj = $this->obj;
        $this->assertEquals([], $obj->warningResults());

        $obj->addResult($result);
        $obj->addResult($result2);
        $result_obj = new ValidatorResult($result);
        $this->assertEquals([$result_obj], $obj->warningResults());
    }

    public function testNoticeResults()
    {
        $result = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::NOTICE,
            'message' => 'foo'
        ];
        $result2 = [
            'ident'   => 'foo',
            'level'   => ValidatorClass::ERROR,
            'message' => 'bar'
        ];
        $obj = $this->obj;
        $this->assertEquals([], $obj->noticeResults());

        $obj->addResult($result);
        $obj->addResult($result2);
        $result_obj = new ValidatorResult($result);
        $this->assertEquals([$result_obj], $obj->noticeResults());
    }

    public function testMerge()
    {
        $result = [
            'ident'   => 'bar',
            'level'   => ValidatorClass::NOTICE,
            'message' => 'foo'
        ];
        $result2 = [
            'ident'   => 'foo',
            'level'   => ValidatorClass::ERROR,
            'message' => 'bar'
        ];
        $result_obj = new ValidatorResult($result);
        $result2_obj = new ValidatorResult($result2);
        $obj = $this->obj;
        $obj2 = new ValidatorClass($this->model);

        $obj->addResult($result);
        $obj2->addResult($result2);
        $obj->merge($obj2);

        $this->assertEquals(
            [
                ValidatorClass::NOTICE => [$result_obj],
                ValidatorClass::ERROR => [$result2_obj]
            ],
            $obj->results()
        );
    }
}
