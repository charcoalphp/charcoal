<?php


namespace Charcoal\Tests\Translation;

use \Charcoal\Translation\Catalog as Catalog;
use \Charcoal\Translation\TranslationString as TranslationString;

class CatalogTest extends \PHPUnit_Framework_TestCase
{
    /**
    *
    */
    public function testOffsetExists()
    {
        $obj = new Catalog();
        $this->assertFalse(isset($obj['test']));
        $obj->add_translation('test', ['en'=>'foo']);
        $this->assertTrue(isset($obj['test']));

        $this->setExpectedException('\InvalidArgumentException');
        $ret = isset($obj[0]);

    }

    /**
    *
    */
    public function testOffsetGet()
    {
        $obj = new Catalog();
        $this->assertEquals($obj->tr('test'), $obj['test']);
        $obj->add_translation('test', ['en'=>'foo', 'fr'=>'bar']);

        $obj->set_lang('en');
        $this->assertEquals('foo', $obj['test']);
        $obj->set_lang('fr');
        $this->assertEquals('bar', $obj['test']);

        $this->setExpectedException('\InvalidArgumentException');
        $ret = $obj[0];
    }

    /**
    *
    */
    public function testOffsetSetString()
    {
        $obj = new Catalog();
        $obj->set_lang('en');
        $obj['test'] = 'foo';
        $this->assertEquals('foo', $obj->tr('test', 'en'));

        $obj->set_lang('fr');
        $obj['test'] = 'bar';
        $this->assertEquals('bar', $obj->tr('test', 'fr'));

        $this->setExpectedException('\InvalidArgumentException');
        $obj[0] = 'foobar';
    }

    /**
    *
    */
    public function testOffsetSetInvalidValue()
    {
        $obj = new Catalog();
        $this->setExpectedException('\InvalidArgumentException');
        $obj['foo'] = false;
    }

    /**
    *
    */
    public function testOffsetUnset()
    {
        $obj = new Catalog();
        $this->assertFalse(isset($obj['foobar']));
        $obj['foobar'] = ['en'=>'a', 'fr'=>'b'];
        $this->assertTrue(isset($obj['foobar']));

        unset($obj['foobar']);
        $this->assertFalse(isset($obj['foobar']));

        $this->setExpectedException('\InvalidArgumentException');
        unset($obj[0]);
    }

    /**
    *
    */
    public function testSetLang()
    {
        $obj = new Catalog();
        $ret = $obj->set_lang('fr');
        $this->assertSame($ret, $obj);
        $this->assertEquals('fr', $obj->lang());
    }

    /**
    *
    */
    public function testLangUnsetReturnsDefault()
    {
        $obj = new Catalog();
        $this->assertEquals('en', $obj->lang());
    }

    /**
    *
    */
    public function testAddTranslation()
    {
        $obj = new Catalog();
        $ret = $obj->add_translation('test', ['en'=>'foo', 'fr'=>'bar']);
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->tr('test', 'en'));
        $this->assertEquals('bar', $obj->tr('test', 'fr'));
    }

    /**
    *
    */
    public function testAddTranslationIdentStringException()
    {
        $obj = new Catalog();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_translation(false, []);
    }

    /**
    * Test the `add_translation(()` method with a `TranslationString`
    * object as "values" (or "translations") parameter.
    */
    public function testAddTranslationTranslationString()
    {
        $obj = new Catalog();
        $str = new TranslationString(['en'=>'en string', 'fr'=>'chaîne fr']);

        $obj->add_translation('str', $str);

        $this->assertEquals('en string', $obj->tr('str', 'en'));
        $this->assertEquals('chaîne fr', $obj->tr('str', 'fr'));
    }


    /**
    * Ensures calling `tr()` with an ident that was never previously set
    * returns the ident itself.
    */
    public function testTrReturnsIdentIfUnset()
    {
        $obj = new Catalog();
        $this->assertEquals('unset_ident', $obj->tr('unset_ident'));
    }
}
