<?php

namespace Charcoal\Tests\Translator;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From `charcoal-translator`
use Charcoal\Translator\LocalesConfig;

/**
 *
 */
class LocalesConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * Object under test.
     * @var LanguageConfig
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new LocalesConfig();
    }

    public function testDefaultsArrayAccess()
    {
        $this->assertArrayHasKey('en', $this->obj['languages']);
        $this->assertEquals('en', $this->obj['default_language']);
        $this->assertEquals(['en'], $this->obj['fallback_languages']);
        $this->assertFalse($this->obj['auto_detect']);
    }

    public function testSetLanguages()
    {
        $langs = [
            'foo' => [
                'locale'=>'foo-FOO'
            ]
        ];
        $ret = $this->obj->setLanguages($langs);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($langs, $this->obj->languages());

        $langs = [
            'bar' => [
                'locale'=>'bar-BAR'
            ]
        ];
        $this->obj['languages'] = $langs;
        $this->assertEquals($langs, $this->obj['languages']);
    }

    public function testSetDefaultLanguage()
    {
        $ret = $this->obj->setDefaultLanguage('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->defaultLanguage());

        $this->obj['default_language'] = 'bar';
        $this->assertEquals('bar', $this->obj['default_language']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setDefaultLanguage(false);
    }

    public function testSetFallbackLanguages()
    {
        $ret = $this->obj->setFallbackLanguages(['foo']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'], $this->obj->fallbackLanguages());

        $this->obj['fallback_languages'] = ['bar'];
        $this->assertEquals(['bar'], $this->obj['fallback_languages']);
    }
}
