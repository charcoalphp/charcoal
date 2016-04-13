<?php

namespace Charcoal\Tests\Property;


/**
 * ## TODOs
 * - 2015-03-12:
 */
class AbstractPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Property\AbstractProperty');
    }

    public function testSetIdent()
    {
        $this->assertEquals('', $this->obj->ident());
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->ident());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setIdent([]);
    }

    public function testSetInputVal()
    {
        $this->assertEquals('', $this->obj->inputVal(null));

        $this->assertEquals('foo', $this->obj->inputVal('foo'));

        $ret = $this->obj->inputVal(['foo'=>'bar']);
        $this->assertEquals('{"foo":"bar"}', str_replace(["\n", "\r", "\t", ' '], '', $ret));
    }

    public function testSetInputValL10n()
    {
        $this->obj->setL10n(true);
    }

    public function testSetInputValMultiple()
    {
        $this->obj->setMultiple(true);
    }

    public function testSetInputValL10nMultiple()
    {
        $this->obj->setL10n(true);
        $this->obj->setMultiple(true);
    }

    public function testSetL10n()
    {
        $this->assertFalse($this->obj->l10n());

        $ret = $this->obj->setL10n(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->l10n());
    }

    public function testSetHidden()
    {
        $this->assertFalse($this->obj->hidden());

        $ret = $this->obj->setHidden(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->hidden());
    }

    public function testSetMultiple()
    {
        $this->assertFalse($this->obj->multiple());

        $ret = $this->obj->setMultiple(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->multiple());
    }

    public function testMultipleSeparator()
    {
        $this->assertEquals(',', $this->obj->multipleSeparator());

        $this->obj->setMultipleOptions([
            'separator'=>'/'
        ]);
        $this->assertEquals('/', $this->obj->multipleSeparator());
    }

    public function testSetRequired()
    {
        $this->assertFalse($this->obj->required());

        $ret = $this->obj->setRequired(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->required());
    }

    public function testSetUnique()
    {
        $this->assertFalse($this->obj->unique());

        $ret = $this->obj->setUnique(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->unique());
    }

    public function testSetAllowNull()
    {
        $this->assertTrue($this->obj->allowNull());

        $ret = $this->obj->setAllowNull(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->allowNull());
    }

    public function testSetStorable()
    {
        $this->assertTrue($this->obj->storable());

        $ret = $this->obj->setStorable(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->storable());
    }

}
