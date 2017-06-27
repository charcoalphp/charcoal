<?php

namespace Charcoal\Tests\Translator;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From `charcoal-translator`
use Charcoal\Translator\LocalesManager;

/**
 *
 */
class LocalesManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Object under test.
     * @var LanguageConfig
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new LocalesManager([
            'locales' => [
                'foo' => [],
                'bar' => [],
                'baz' => ['active'=>false]
            ],
            'fallback_languages'=>['foo', 'bar']
        ]);
    }

    public function testConstructorWithDefaultLanguage()
    {
        $this->obj = new LocalesManager([
            'locales' => [
                'foo' => [],
                'bar' => [],
                'baz' => ['active'=>false]
            ],
            'default_language'=>'bar'
        ]);
        $this->assertEquals('bar', $this->obj->currentLocale());
    }

    public function testConstructorInvalidDefaultLanguageThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = new LocalesManager([
            'locales' => [
                'foo' => []
            ],
            'default_language'=>'bar'
        ]);
    }

    public function testConstructorNoActiveLocalesThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = new LocalesManager([
            'locales' => []
        ]);
    }

    public function testLocales()
    {
        $locales = $this->obj->locales();
        $this->assertArrayHasKey('foo', $locales);
        $this->assertArrayHasKey('bar', $locales);

        // Also assert that inactive locales are skipped
        $this->assertArrayNotHasKey('baz', $locales);
    }

    public function testAvailableLocales()
    {
        $this->assertEquals(['foo','bar'], $this->obj->availableLocales());
    }

    public function testSetCurrentLocale()
    {
        $this->assertEquals('foo', $this->obj->currentLocale());

        $this->obj->setCurrentLocale('bar');
        $this->assertEquals('bar', $this->obj->currentLocale());

        $this->obj->setCurrentLocale(null);
        $this->assertEquals('foo', $this->obj->currentLocale());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setCurrentLocale('foobazbar');
    }
}
