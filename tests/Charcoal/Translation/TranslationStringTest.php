<?php

namespace Charcoal\Tests\Translation;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Translation\TranslationString as TranslationString;

class TranslationStringTest extends \PHPUnit_Framework_TestCase
{
    /**
    *
    */
    public function testConstructorWithParam()
    {
        $obj = new TranslationString('foobar');
        $this->assertEquals('foobar', $obj->val());
    }

    /**
    *
    */
    public function testMagicCall()
    {
        $obj = new TranslationString(
            [ 'fr' => 'foobar' ],
            [
                'languages' => [ 'fr' ]
            ]
        );
        $this->assertEquals('foobar', $obj->val('fr'));
        $this->assertEquals('foobar', $obj->fr());

        $this->setExpectedException('\Exception');
        $obj->invalid_lang_foo();
    }

    /**
    *
    */
    public function testMagicString()
    {
        $obj = new TranslationString('foo bar baz');
        ob_start();
        echo $obj;
        $val = ob_get_clean();
        $this->assertEquals('foo bar baz', $val);
    }

    /**
    *
    */
    public function testSetVal()
    {
        $obj = new TranslationString(
            null,
            [
                'languages' => [ 'en', 'fr' ]
            ]
        );
        $ret = $obj->set_val('foo bar');

        $this->assertSame($ret, $obj);
        $this->assertEquals('foo bar', $obj->val());

        $ret = $obj->set_val([ 'en' => 'foo bar', 'fr' => 'bar baz' ]);
        $this->assertEquals('foo bar', $obj->en());
        $this->assertEquals('bar baz', $obj->fr());

        $clone = new TranslationString(
            [ 'en' => 'foo', 'fr' => 'bar' ],
            [
                'languages' => [ 'en', 'fr' ]
            ]
        );
        $obj->set_val($clone);
        $this->assertEquals('foo', $obj->en());
        $this->assertEquals('bar', $obj->fr());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_val(false);
    }

    /**
    *
    */
    public function testAddVal()
    {
        $obj = new TranslationString();
    }

    /**
    *
    */
    public function testAddVallInvalidLangType()
    {
        $obj = new TranslationString();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_val(false, 'foo');
    }

    /**
    *
    */
    public function testAddVallInvalidString()
    {
        $obj = new TranslationString();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_val('en', [1,2,3]);
    }

    /**
    *
    */
    public function testValInvalidLanguage()
    {
        $obj = new TranslationString();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->val('invalid-lang-zzz');
    }

    /**
    *
    */
    public function testValGetDefaultLanguage()
    {
        $obj = new TranslationString(
            null,
            [
                'languages' => [ 'en', 'fr' ]
            ]
        );
        $obj->set_val([ 'en' => 'foo' ]);

        $this->assertEquals('foo', $obj->val('fr'));
    }

    /**
    *
    */
    public function testValEmptyStringIfUnset()
    {
        $obj = new TranslationString();
        $this->assertSame('', $obj->val('en'));
        $this->assertSame('', $obj->val('en'));
    }

    /**
    *
    */
    public function testSetCurrentLanguage()
    {
        $obj = new TranslationString(
            [ 'en' => 'foo bar', 'fr' => 'bar baz' ],
            [
                'languages' => [ 'en', 'fr' ]
            ]
        );
        $ret = $obj->set_current_language('en');
        $this->assertSame($ret, $obj);
        $this->assertEquals('en', $obj->current_language());

        $this->assertEquals('foo bar', $obj->val());

        $obj->set_current_language('fr');
        $this->assertEquals('bar baz', $obj->val());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_current_language(false);
    }

    /**
    *
    */
    public function testLanguageUnsetReturnsDefault()
    {
        $obj = new TranslationString();
        $this->assertEquals('en', $obj->current_language());
    }

    /**
    *
    */
    public function testSetValStringDefaultLanguage()
    {
        $obj = new TranslationString(
            null,
            [
                'languages' => [ 'en', 'fr' ]
            ]
        );

        $obj->set_current_language('en');
        $obj->set_val('foo');

        $obj->set_current_language('fr');
        $obj->set_val('bar');

        $this->assertEquals(['en'=>'foo', 'fr'=>'bar'], $obj->all());
    }
}
