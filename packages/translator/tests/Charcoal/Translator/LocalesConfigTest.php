<?php

namespace Charcoal\Tests\Translator;

use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesConfig;
use Charcoal\Tests\Translator\AbstractTestCase;

/**
 *
 */
class LocalesConfigTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\Translator\LocalesConfig|array $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $this->obj = new LocalesConfig();
    }

    public function testDefaultsArrayAccess(): void
    {
        $this->assertArrayHasKey('en', $this->obj['languages']);
        $this->assertEquals('en', $this->obj['default_language']);
        $this->assertEquals(['en'], $this->obj['fallback_languages']);
        $this->assertFalse($this->obj['auto_detect']);
    }

    public function testSetLanguages(): void
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

    public function testSetDefaultLanguage(): void
    {
        $ret = $this->obj->setDefaultLanguage('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->defaultLanguage());

        $this->obj['default_language'] = 'bar';
        $this->assertEquals('bar', $this->obj['default_language']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setDefaultLanguage(false);
    }

    public function testSetFallbackLanguages(): void
    {
        $ret = $this->obj->setFallbackLanguages(['foo']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'], $this->obj->fallbackLanguages());

        $this->obj['fallback_languages'] = ['bar'];
        $this->assertEquals(['bar'], $this->obj['fallback_languages']);
    }
}
