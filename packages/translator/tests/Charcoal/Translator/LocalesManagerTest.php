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
     */
    private \Charcoal\Translator\LocalesManager $obj;

    /**
     * Set up the test.
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

    public function testConstructorWithDefaultLanguage(): void
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

    public function testConstructorDefaultLanguageWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LocalesManager([
            'locales' => [
                'foo' => []
            ],
            'default_language' => false
        ]);
    }

    public function testConstructorDefaultLanguageWithInvalidLocale(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LocalesManager([
            'locales' => [
                'foo' => []
            ],
            'default_language' => 'bar'
        ]);
    }

    public function testConstructorWithoutActiveLocales(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LocalesManager([
            'locales' => []
        ]);
    }

    public function testLocales(): void
    {
        $locales = $this->obj->locales();
        $this->assertArrayHasKey('foo', $locales);
        $this->assertArrayHasKey('bar', $locales);

        // Also assert that inactive locales are skipped
        $this->assertArrayNotHasKey('baz', $locales);
    }

    #[\PHPUnit\Framework\Attributes\RequiresPhp('>= 8.1.0')]
    public function testSortedLocalesInPhp7(): void
    {
        $obj = $this->getLocalesManagerForSortedLocales();

        $this->assertEquals([ 'xyz', 'zyx', 'qux', 'foo', 'bar' ], $obj->availableLocales());
    }

    public function getLocalesManagerForSortedLocales(): \Charcoal\Translator\LocalesManager
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

    public function testAvailableLocales(): void
    {
        $this->assertEquals([ 'foo', 'bar' ], $this->obj->availableLocales());
    }

    public function testSetCurrentLocale(): void
    {
        $this->assertEquals('foo', $this->obj->currentLocale());

        $this->obj->setCurrentLocale('bar');
        $this->assertEquals('bar', $this->obj->currentLocale());

        $this->obj->setCurrentLocale(null);
        $this->assertEquals('foo', $this->obj->currentLocale());
    }

    public function testSetCurrentLocaleWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setCurrentLocale(false);
    }

    public function testSetCurrentLocaleWithInvalidLocale(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setCurrentLocale('qux');
    }
}
