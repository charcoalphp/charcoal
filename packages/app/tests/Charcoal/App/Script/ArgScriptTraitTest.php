<?php

namespace Charcoal\Tests\App\Script;

use InvalidArgumentException;

use Charcoal\App\Script\ArgScriptTrait;
use Charcoal\Tests\AbstractTestCase;

class ArgScriptTraitTest extends AbstractTestCase
{
    public function testParseAsArrayFromString()
    {
        $obj = new ArgScriptStub();
        $this->assertSame([ 'a', 'b', 'c' ], $obj->exposeParseAsArray('a, b, c'));
    }

    public function testParseAsArrayFromArray()
    {
        $obj = new ArgScriptStub();
        $this->assertSame([ 'x' ], $obj->exposeParseAsArray([ 'x' ]));
    }

    public function testParseAsArrayRejectsInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        (new ArgScriptStub())->exposeParseAsArray(123);
    }

    public function testParseAsArrayRejectsBadDelimiter()
    {
        $this->expectException(InvalidArgumentException::class);
        (new ArgScriptStub())->exposeParseAsArray('a', false);
    }
}

class ArgScriptStub
{
    use ArgScriptTrait;

    public function exposeParseAsArray($var, $delimiter = '[\s,]+')
    {
        return $this->parseAsArray($var, $delimiter);
    }
}
