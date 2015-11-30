<?php

namespace Charcoal\Tests\Translation;

// Intra-module (`charcoal-core`) dependencies
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
            'languages'        => [ 'en', 'fr' ],
            'default_language' => 'fr'
        ]);
        $this->assertSame($ret, $obj);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_default_language('es');

        $this->assertEquals('es', $obj->default_language());
    }

    public function testSetLanguage()
    {
        $obj = new TranslationConfig([
            'languages' => [ 'en', 'fr' ]
        ]);
        $this->assertSame('en', $obj->current_language());
        $ret = $obj->set_current_language('fr');
        $this->assertSame($ret, $obj);
        $this->assertEquals('fr', $obj->current_language());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_current_language('foobar-lang');
    }

    public function testSetDefaultLanguage()
    {
        $obj = new TranslationConfig();
    }
}
