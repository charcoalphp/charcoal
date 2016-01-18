<?php

namespace Charcoal\Tests\Translation;

// Intra-module (`charcoal-core`) dependencies
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
        $obj->setConfig([
            'languages' => [ 'en' ]
        ]);

        $this->assertFalse(isset($obj['test']));

        $obj->addEntry('test', [ 'en' => 'foo' ]);

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
        $obj->setConfig([
            'languages' => [ 'en', 'fr' ]
        ]);

        $this->assertEquals($obj->translate('test'), $obj['test']);
        $obj->addEntry('test', [ 'en' => 'foo', 'fr' => 'bar' ]);

        $obj->setCurrentLanguage('en');
        $this->assertEquals('foo', $obj['test']);

        $obj->setCurrentLanguage('fr');
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
        $obj->setConfig([
            'languages' => [ 'en', 'fr' ]
        ]);

        $obj->setCurrentLanguage('en');
        $obj['test'] = 'foo';
        $this->assertEquals('foo', $obj->translate('test', 'en'));

        $obj->setCurrentLanguage('fr');
        $obj['test'] = 'bar';
        $this->assertEquals('bar', $obj->translate('test', 'fr'));

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
        $obj->setConfig([
            'languages' => [ 'en', 'fr' ]
        ]);

        $this->assertFalse(isset($obj['foobar']));

        $obj['foobar'] = [ 'en' => 'a', 'fr' => 'b' ];
        $this->assertTrue(isset($obj['foobar']));

        unset($obj['foobar']);
        $this->assertFalse(isset($obj['foobar']));

        $this->setExpectedException('\InvalidArgumentException');
        unset($obj[0]);
    }

    /**
    *
    */
    public function testSetLanguage()
    {
        $obj = new Catalog();

        $this->setExpectedException('\InvalidArgumentException');
        $ret = $obj->setCurrentLanguage('jp');

        $this->assertSame($ret, $obj);

        $this->assertEquals('jp', $obj->currentLanguage());
    }

    /**
    *
    */
    public function testLangUnsetReturnsDefault()
    {
        $obj = new Catalog();
        $this->assertEquals('en', $obj->currentLanguage());
    }

    /**
    *
    */
    public function testAddTranslation()
    {
        $obj = new Catalog();
        $obj->setConfig([
            'languages' => [ 'en', 'fr' ]
        ]);
        $ret = $obj->addEntry('test', [ 'en' => 'foo', 'fr' => 'bar' ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->translate('test', 'en'));
        $this->assertEquals('bar', $obj->translate('test', 'fr'));
    }

    /**
    *
    */
    public function testAddTranslationIdentStringException()
    {
        $obj = new Catalog();

        $this->setExpectedException('\InvalidArgumentException');
        $obj->addEntry(false, []);
    }

    /**
    * Test the `addEntry()` method with a `TranslationString`
    * object as "values" (or "translations") parameter.
    */
    public function testAddTranslationTranslationString()
    {
        $cfg = [
            'languages' => [ 'en', 'fr' ]
        ];
        $obj = new Catalog();
        $obj->setConfig($cfg);

        $str = new TranslationString([ 'en' => 'en string', 'fr' => 'chaîne fr'], $cfg);

        $obj->addEntry('str', $str);

        $this->assertEquals('en string', $obj->translate('str', 'en'));
        $this->assertEquals('chaîne fr', $obj->translate('str', 'fr'));
    }

    /**
    * Ensures calling `translate()` with an ident that was never previously set
    * returns the ident itself.
    */
    public function testTranslateReturnsIdentIfUnset()
    {
        $obj = new Catalog();
        $this->assertEquals('unset_ident', $obj->translate('unset_ident'));
    }
}
