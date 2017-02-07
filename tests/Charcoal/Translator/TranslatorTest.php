<?php

namespace Charcoal\Tests\Translator;

use PHPUnit_Framework_TestCase;

use Symfony\Component\Translation\MessageSelector;

// Local Dependencies
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translation;
use Charcoal\Translator\Translator;

/**
 *
 */
class TranslatorTest extends PHPUnit_Framework_TestCase
{
    private $languageManager;
    private $obj;

    public function setUp()
    {
        $this->languageManager = new LocalesManager([
            'locales' => [
                'foo' => ['locale'=>'foo-FOO'],
                'bar' => ['locale'=>'bar-BAR']
            ],
            'default_language' => 'foo',
            'fallback_languages' => ['foo']
        ]);
        $this->obj = new Translator([
            'locale'            => 'foo',
            'message_selector'  => new MessageSelector(),
            'cache_dir'         => null,
            'debug'             => false,
            'manager'  => $this->languageManager
        ]);
    }

    /**
     *
     */
    public function testTranslation()
    {
        $ret = $this->obj->translation('foo');
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('foo', (string)$ret);

        $translation = clone($ret);
        $ret = $this->obj->translation($translation);
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('foo', (string)$ret);

        $ret = $this->obj->translation([
            'foo' => 'foobar',
            'bar' => 'barfoo'
        ]);
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('foobar', (string)$ret);
    }

    /**
     * @dataProvider invalidTranslationsProvider
     */
    public function testTranslationInvalidValuesReturnNull($val)
    {
        $this->assertNull($this->obj->translation($val));
    }

    /**
     *
     */
    public function testTranslate()
    {
        $ret = $this->obj->translate('foo');
        $this->assertEquals('foo', $ret);

        $translation = $this->obj->translation('foo');
        $this->assertEquals('foo', $this->obj->translate($translation));

        $this->assertEquals('foobar', $this->obj->translate([
            'foo' => 'foobar',
            'bar' => 'barfoo'
        ]));
    }

    /**
     * @dataProvider invalidTranslationsProvider
     */
    public function testTranslateInvalidValuesReturnEmptyString($val)
    {
        $this->assertEquals('', $this->obj->translate($val));
    }

    /**
     *
     */
    public function testSetLocaleSetLanguageManagerCurrentLanguage()
    {
        $this->obj->setLocale('bar');
        $this->assertEquals('bar', $this->languageManager->currentLocale());
    }

    public function testLocales()
    {
        $this->assertArrayHasKey('foo', $this->obj->locales());
        $this->assertArrayHasKey('bar', $this->obj->locales());
        $this->assertArrayNotHasKey('baz', $this->obj->locales());
    }

    public function testAvailableLocales()
    {
        $this->assertEquals(['foo', 'bar'], $this->obj->availableLocales());
    }

    /**
     *
     */
    public function invalidTranslationsProvider()
    {
        return [
            [null],
            [0],
            [1],
            [true],
            [false],
            [[]],
            [['foo', 'bar']],
            [[[]]],
            ['']
        ];
    }
}
