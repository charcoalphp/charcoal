<?php

namespace Charcoal\Tests\Core;

use \Charcoal\Core\StringFormat as StringFormat;

class StringFormatTest extends \PHPUnit_Framework_TestCase
{


    public function testConstructor()
    {
        $obj = new StringFormat();
        $this->assertInstanceOf('\Charcoal\Core\StringFormat', $obj);
        $this->assertEquals('', $obj->string());
        $this->assertTrue($obj->unicode());

        $obj = new StringFormat('Foo bar');
        $this->assertEquals('Foo bar', $obj->string());
    }

    public function testMagicString()
    {
        $obj = new StringFormat('Foo bar');
        ob_start();
        echo $obj;
        $ret = ob_get_clean();
        $this->assertEquals('Foo bar', $ret);
    }

    public function testSetString()
    {
        $obj = new StringFormat();
        $ret = $obj->set_string('Foo bar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('Foo bar', $obj->string());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_string(false);
    }

    public function testSetUnicode()
    {
        $obj = new StringFormat();
        $ret=  $obj->set_unicode(false);
        $this->assertSame($ret, $obj);
        $this->assertNotTrue($obj->unicode());
    }

    public function testStripTags()
    {
        $obj = new StringFormat('<p>Test</p>');
        $ret = $obj->strip_tags();
        $this->assertSame($ret, $obj);
        $this->assertEquals('Test', $obj->string());
    }

    /**
    * @dataProvider providerUnaccents
    */
    public function testUnaccents($str, $res)
    {
        $obj = new StringFormat($str);
        $ret = $obj->unaccents();
        $this->assertSame($ret, $obj);
        $this->assertEquals($res, $obj->string());
    }

    public function providerUnaccents()
    {
        return [
            ['æ œ Œ Æ', 'ae oe OE AE'],
            ['š', 's'],
            ['fóø bår FÓØ BÅR', 'foo bar FOO BAR'],
            ['àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY']
        ];
    }

    public function testAlphanumeric()
    {
        $obj = new StringFormat('This "string" contaïns non-àlphanumerical #characters');
        $ret = $obj->alphanumeric();
        $this->assertSame($ret, $obj);
        $this->assertEquals('This string contaïns nonàlphanumerical characters', $obj->string());
    }

    public function testAlphanumericNonUnicode()
    {
        $obj = new StringFormat('This "string" contaïns non-àlphanumerical #characters');
        $obj->set_unicode(false);
        $ret = $obj->alphanumeric();
        $this->assertSame($ret, $obj);
        $this->assertEquals('This string contans nonlphanumerical characters', $obj->string());
    }
}
