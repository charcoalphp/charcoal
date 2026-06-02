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
    public function testSetData(): void
    {
        $obj = new ValidatorResult();
        $ret = $obj->setData([]);
        $this->assertSame($ret, $obj);
    }

    public function testSetIdent(): void
    {
        $obj = new ValidatorResult();
        $this->assertEquals(null, $obj->ident());

        $ret = $obj->setIdent('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->ident());

        $this->expectException(InvalidArgumentException::class);
        $obj->setIdent(false);
    }

    public function testSetLevel(): void
    {
        $obj = new ValidatorResult();
        $this->assertEquals(null, $obj->level());

        $ret = $obj->setLevel('warning');
        $this->assertSame($ret, $obj);
        $this->assertEquals('warning', $obj->level());

        $this->expectException(InvalidArgumentException::class);
        $obj->setLevel(false);
    }

    public function testSetLevelWithInvalidLevelsThrowException(): void
    {
        $obj = new ValidatorResult();
        $this->expectException(InvalidArgumentException::class);
        $obj->setLevel('foo');
    }

    public function testSetMessage(): void
    {
        $obj = new ValidatorResult();
        $this->assertEquals('', $obj->message());

        $ret = $obj->setMessage('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->message());

        $this->expectException(InvalidArgumentException::class);
        $obj->setMessage(false);
    }

    public function testSetTs(): void
    {
        $obj = new ValidatorResult();
        $ret = $obj->setTs('2015-01-01 00:00:00');
        $this->assertSame($ret, $obj);

        $this->assertInstanceOf('\DateTime', $obj->ts());

        $this->expectException(InvalidArgumentException::class);
        $obj->setTs(false);
    }
}
