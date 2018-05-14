<?php

namespace Charcoal\Tests\Translator;

use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class LocalesManagerTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var LocalesManager
     */
    private $obj;

    /**
     * Set up the test.
     *
     * @return void
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

    /**
     * @return void
     */
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

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testConstructorDefaultLanguageWithInvalidType()
    {
        $obj = new LocalesManager([
            'locales' => [
                'foo' => []
            ],
            'default_language' => false
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testConstructorDefaultLanguageWithInvalidLocale()
    {
        $obj = new LocalesManager([
            'locales' => [
                'foo' => []
            ],
            'default_language' => 'bar'
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testConstructorWithoutActiveLocales()
    {
        $obj = new LocalesManager([
            'locales' => []
        ]);
    }

    /**
     * @return void
     */
    public function testLocales()
    {
        $locales = $this->obj->locales();
        $this->assertArrayHasKey('foo', $locales);
        $this->assertArrayHasKey('bar', $locales);

        // Also assert that inactive locales are skipped
        $this->assertArrayNotHasKey('baz', $locales);
    }

    /**
     * @return void
     */
    public function testSortedLocales()
    {
        $this->obj = new LocalesManager([
            'locales' => [
                'foo' => [ 'priority' => 2 ],
                'bar' => [ 'priority' => 3 ],
                'baz' => [ 'priority' => 1, 'active' => false ],
                'qux' => [ 'priority' => 1 ],
            ]
        ]);

        $this->assertEquals([ 'qux', 'foo', 'bar' ], $this->obj->availableLocales());
    }

    /**
     * @return void
     */
    public function testAvailableLocales()
    {
        $this->assertEquals([ 'foo', 'bar' ], $this->obj->availableLocales());
    }

    /**
     * @return void
     */
    public function testSetCurrentLocale()
    {
        $this->assertEquals('foo', $this->obj->currentLocale());

        $this->obj->setCurrentLocale('bar');
        $this->assertEquals('bar', $this->obj->currentLocale());

        $this->obj->setCurrentLocale(null);
        $this->assertEquals('foo', $this->obj->currentLocale());
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testSetCurrentLocaleWithInvalidType()
    {
        $this->obj->setCurrentLocale(false);
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testSetCurrentLocaleWithInvalidLocale()
    {
        $this->obj->setCurrentLocale('qux');
    }
}
