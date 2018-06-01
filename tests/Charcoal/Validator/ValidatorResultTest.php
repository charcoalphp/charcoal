<?php

namespace Charcoal\Tests\Validator;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Validator\ValidatorResult;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ValidatorResultTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testSetData()
    {
        $obj = new ValidatorResult();
        $ret = $obj->setData([]);
        $this->assertSame($ret, $obj);
    }

    /**
     * @return void
     */
    public function testSetIdent()
    {
        $obj = new ValidatorResult();
        $this->assertEquals(null, $obj->ident());

        $ret = $obj->setIdent('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->ident());

        $this->expectException(InvalidArgumentException::class);
        $obj->setIdent(false);
    }

    /**
     * @return void
     */
    public function testSetLevel()
    {
        $obj = new ValidatorResult();
        $this->assertEquals(null, $obj->level());

        $ret = $obj->setLevel('warning');
        $this->assertSame($ret, $obj);
        $this->assertEquals('warning', $obj->level());

        $this->expectException(InvalidArgumentException::class);
        $obj->setLevel(false);
    }

    /**
     * @return void
     */
    public function testSetLevelWithInvalidLevelsThrowException()
    {
        $obj = new ValidatorResult();
        $this->expectException(InvalidArgumentException::class);
        $obj->setLevel('foo');
    }

    /**
     * @return void
     */
    public function testSetMessage()
    {
        $obj = new ValidatorResult();
        $this->assertEquals('', $obj->message());

        $ret = $obj->setMessage('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->message());

        $this->expectException(InvalidArgumentException::class);
        $obj->setMessage(false);
    }

    /**
     * @return void
     */
    public function testSetTs()
    {
        $obj = new ValidatorResult();
        $ret = $obj->setTs('2015-01-01 00:00:00');
        $this->assertSame($ret, $obj);

        $this->assertInstanceOf('\DateTime', $obj->ts());

        $this->expectException(InvalidArgumentException::class);
        $obj->setTs(false);
    }
}
