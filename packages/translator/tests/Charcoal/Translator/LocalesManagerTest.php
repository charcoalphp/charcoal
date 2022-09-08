<?php

namespace Charcoal\Tests\Translator;

use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
use Charcoal\Tests\Translator\AbstractTestCase;

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
    protected function setUp(): void
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
     * @return void
     */
    public function testConstructorDefaultLanguageWithInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new LocalesManager([
            'locales' => [
                'foo' => []
            ],
            'default_language' => false
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorDefaultLanguageWithInvalidLocale()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new LocalesManager([
            'locales' => [
                'foo' => []
            ],
            'default_language' => 'bar'
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorWithoutActiveLocales()
    {
        $this->expectException(InvalidArgumentException::class);
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
     * @requires PHP >= 7.0
     * @return   void
     */
    public function testSortedLocalesInPhp7()
    {
        $obj = $this->getLocalesManagerForSortedLocales();

        $this->assertEquals([ 'xyz', 'zyx', 'qux', 'foo', 'bar' ], $obj->availableLocales());
    }

    /**
     * @return LocalesManager
     */
    public function getLocalesManagerForSortedLocales()
    {
        return new LocalesManager([
            'locales' => [
                'foo' => [ 'priority' => 2 ],
                'bar' => [ 'priority' => 3 ],
                'baz' => [ 'priority' => 1, 'active' => false ],
                'qux' => [ 'priority' => 1 ],
                'xyz' => [ 'priority' => 0 ],
                'zyx' => [ 'priority' => 0 ],
            ]
        ]);
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
     * @return void
     */
    public function testSetCurrentLocaleWithInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setCurrentLocale(false);
    }

    /**
     * @return void
     */
    public function testSetCurrentLocaleWithInvalidLocale()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setCurrentLocale('qux');
    }
}
