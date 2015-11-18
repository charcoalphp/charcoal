<?php

namespace Charcoal\Tests\Translation;

use \Charcoal\Translation\TranslationConfig as TranslationConfig;

class TranslationConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
    *
    */
    public function testConstructorWithParam()
    {
        $obj = new TranslationConfig();
        $this->assertInstanceOf('\Charcoal\Translation\TranslationConfig', $obj);
    }

    public function testSetData()
    {
        $obj = new TranslationConfig();

        $ret = $obj->set_data([
            'languages'    => [ 'en', 'fr' ],
            'default_lang' => 'fr'
        ]);
        $this->assertSame($ret, $obj);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_default_lang('es');

        $this->assertEquals('es', $obj->default_lang());
    }

    public function testSetLang()
    {
        $obj = new TranslationConfig([
            'languages' => [ 'en', 'fr' ]
        ]);
        $this->assertSame('en', $obj->lang());
        $ret = $obj->set_lang('fr');
        $this->assertSame($ret, $obj);
        $this->assertEquals('fr', $obj->lang());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_lang('foobar-lang');
    }

    public function testSetDefaultLang()
    {
        $obj = new TranslationConfig();
    }
}
