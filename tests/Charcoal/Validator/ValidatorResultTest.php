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

    public function testSetData()
    {
        $obj = new ValidatorResult();
        $ret = $obj->set_data([]);
        $this->assertSame($ret, $obj);

        # $this->setExpectedException('\InvalidArgumentException');
        $this->setExpectedException('\PHPUnit_Framework_Error');
        $obj->set_data(false);
    }

    public function testSetIdent()
    {
        $obj = new ValidatorResult();
        $this->assertEquals(null, $obj->ident());

        $ret = $obj->set_ident('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->ident());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_ident(false);
    }

    public function testSetLevel()
    {
        $obj = new ValidatorResult();
        $this->assertEquals(null, $obj->level());

        $ret = $obj->set_level('warning');
        $this->assertSame($ret, $obj);
        $this->assertEquals('warning', $obj->level());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_level(false);
    }

    public function testSetLevelWithInvalidLevelsThrowException()
    {
        $obj = new ValidatorResult();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_level('foo');
    }

    public function testSetMessage()
    {
        $obj = new ValidatorResult();
        $this->assertEquals('', $obj->message());

        $ret = $obj->set_message('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->message());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_message(false);
    }

    public function testSetTs()
    {
        $obj = new ValidatorResult();
        $ret = $obj->set_ts('2015-01-01 00:00:00');
        $this->assertSame($ret, $obj);

        $this->assertInstanceOf('\Datetime', $obj->ts());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_ts(false);
    }
}
