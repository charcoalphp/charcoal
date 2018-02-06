<?php

namespace Charcoal\Tests\Translator;

use InvalidArgumentException;

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
     * Tested Class.
     *
     * @var LocalesManager
     */
    private $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->obj = new LocalesManager([
            'locales' => [
                'foo' => [],
                'bar' => [],
                'baz' => [ 'active' => false ]
            ],
            'fallback_languages' => [ 'foo', 'bar' ]
        ]);
    }

    public function testConstructorWithDefaultLanguage()
    {
        $this->obj = new LocalesManager([
            'locales' => [
                'foo' => [],
                'bar' => [],
                'baz' => [ 'active' => false ]
            ],
            'default_language' => 'bar'
        ]);
        $this->assertEquals('bar', $this->obj->currentLocale());
        $this->assertEquals('bar', $this->obj->defaultLocale());
    }

    public function testConstructorDefaultLanguageWithInvalidType()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $obj = new LocalesManager([
            'locales' => [
                'foo' => []
            ],
            'default_language' => false
        ]);
    }

    public function testConstructorDefaultLanguageWithInvalidLocale()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $obj = new LocalesManager([
            'locales' => [
                'foo' => []
            ],
            'default_language' => 'bar'
        ]);
    }

    public function testConstructorWithoutActiveLocales()
    {
        $this->setExpectedException(InvalidArgumentException::class);
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
        $this->assertEquals([ 'foo', 'bar' ], $this->obj->availableLocales());
    }

    public function testSetCurrentLocale()
    {
        $this->assertEquals('foo', $this->obj->currentLocale());

        $this->obj->setCurrentLocale('bar');
        $this->assertEquals('bar', $this->obj->currentLocale());

        $this->obj->setCurrentLocale(null);
        $this->assertEquals('foo', $this->obj->currentLocale());
    }

    public function testSetCurrentLocaleWithInvalidType()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->setCurrentLocale(false);
    }

    public function testSetCurrentLocaleWithInvalidLocale()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->setCurrentLocale('qux');
    }
}
