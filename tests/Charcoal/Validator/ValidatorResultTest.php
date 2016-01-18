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
    }

    public function testSetIdent()
    {
        $obj = new ValidatorResult();
        $this->assertEquals(null, $obj->ident());

        $ret = $obj->setIdent('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->ident());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setIdent(false);
    }

    public function testSetLevel()
    {
        $obj = new ValidatorResult();
        $this->assertEquals(null, $obj->level());

        $ret = $obj->setLevel('warning');
        $this->assertSame($ret, $obj);
        $this->assertEquals('warning', $obj->level());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setLevel(false);
    }

    public function testSetLevelWithInvalidLevelsThrowException()
    {
        $obj = new ValidatorResult();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->setLevel('foo');
    }

    public function testSetMessage()
    {
        $obj = new ValidatorResult();
        $this->assertEquals('', $obj->message());

        $ret = $obj->setMessage('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->message());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMessage(false);
    }

    public function testSetTs()
    {
        $obj = new ValidatorResult();
        $ret = $obj->setTs('2015-01-01 00:00:00');
        $this->assertSame($ret, $obj);

        $this->assertInstanceOf('\DateTime', $obj->ts());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setTs(false);
    }
}
