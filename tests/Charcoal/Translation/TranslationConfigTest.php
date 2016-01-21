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

        $ret = $obj->setData([
            'languages' => [ 'en', 'fr' ]
        ]);
        $this->assertSame($ret, $obj);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setDefaultLanguage('es');

        $this->assertEquals('es', $obj->defaultLanguage());
    }

    public function testSetLanguage()
    {
        $obj = new TranslationConfig([
            'languages' => [ 'en', 'fr' ]
        ]);
        $this->assertSame('en', $obj->currentLanguage());
        $ret = $obj->setCurrentLanguage('fr');
        $this->assertSame($ret, $obj);
        $this->assertEquals('fr', $obj->currentLanguage());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setCurrentLanguage('foobar-lang');
    }

    public function testSetDefaultLanguage()
    {
        $obj = new TranslationConfig();
    }
}
