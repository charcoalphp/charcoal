<?php

namespace Charcoal\Tests\Cache;

use InvalidArgumentException;

// From 'charcoal-cache'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Cache\CacheConfig;

/**
 * Test CacheConfig
 *
 * @coversDefaultClass \Charcoal\Cache\CacheConfig
 */
class CacheConfigTest extends AbstractTestCase
{
    /**
     * @var CacheConfig
     */
    public $cfg;

    /**
     * Create the CacheConfig instance.
     */
    public function setUp(): void
    {
        $this->cfg = $this->configFactory();
    }

    /**
     * Create a new CacheConfig instance.
     *
     * @param  array $args Parameters for the initialization of a CacheConfig.
     * @return CacheConfig
     */
    public function configFactory(array $args = [])
    {
        return new CacheConfig($args);
    }

    /**
     * @covers ::defaults
     * @covers ::active
     * @covers ::types
     * @covers ::defaultTypes
     * @covers ::defaultTtl
     * @covers ::prefix
     */
    public function testDefaults()
    {
        $this->assertEquals('charcoal', CacheConfig::DEFAULT_NAMESPACE);
        $this->assertEquals((60 * 60), CacheConfig::HOUR_IN_SECONDS);
        $this->assertEquals((60 * 60 * 24), CacheConfig::DAY_IN_SECONDS);
        $this->assertEquals((60 * 60 * 24 * 7), CacheConfig::WEEK_IN_SECONDS);

        $defaults = $this->cfg->defaults();

        $this->assertArrayHasKey('active', $defaults);
        $this->assertEquals($defaults['active'], $this->cfg->active());

        $this->assertArrayHasKey('types', $defaults);
        $this->assertEquals($defaults['types'], $this->cfg->types());
        $this->assertEquals($defaults['types'], $this->cfg->defaultTypes());

        $this->assertArrayHasKey('default_ttl', $defaults);
        $this->assertEquals($defaults['default_ttl'], $this->cfg->defaultTtl());

        $this->assertArrayHasKey('prefix', $defaults);
        $this->assertEquals($defaults['prefix'], $this->cfg->prefix());
    }

    /**
     * @covers ::setActive
     * @covers ::active
     */
    public function testActive()
    {
        // Chainable
        $that = $this->cfg->setActive(false);
        $this->assertSame($this->cfg, $that);

        // Mutated State
        $this->assertFalse($this->cfg->active());
    }

    /**
     * @covers ::setTypes
     * @covers ::types
     */
    public function testReplaceDrivers()
    {
        // Chainable
        $that = $this->cfg->setTypes([ 'memcache', 'noop' ]);
        $this->assertSame($this->cfg, $that);

        // Mutated State
        $types = $this->cfg->types();
        $this->assertEquals([ 'memcache', 'noop', 'memory' ], $types);
    }

    /**
     * @covers ::types
     */
    public function testUniqueDrivers()
    {
        $this->cfg->setTypes([ 'memcache', 'memory', 'file', 'memcache' ]);

        $types = $this->cfg->types();
        $this->assertEquals([ 'memcache', 'memory', 'file' ], $types);
    }

    /**
     * @covers ::addTypes
     * @covers ::addType
     * @covers ::types
     */
    public function testAddDrivers()
    {
        // Chainable
        $that = $this->cfg->addTypes([ 'memcache', 'noop' ]);
        $this->assertSame($this->cfg, $that);

        // Mutated State
        $types = $this->cfg->types();
        $this->assertContains('memory', $types);
        $this->assertContains('memcache', $types);
        $this->assertContains('noop', $types);
    }

    /**
     * @covers ::validTypes
     * @covers ::addType
     */
    public function testAddDriverOnInvalidType()
    {
        $this->expectExceptionMessage('Invalid cache type: "foobar"');
        $this->expectException(InvalidArgumentException::class);
        $this->cfg->addType('foobar');
    }

    /**
     * @covers ::setDefaultTtl
     * @covers ::defaultTtl
     */
    public function testDefaultTtl()
    {
        // Chainable
        $that = $this->cfg->setDefaultTtl(42);
        $this->assertSame($this->cfg, $that);

        // Mutated State
        $this->assertEquals(42, $this->cfg->defaultTtl());
    }

    /**
     * @covers ::setDefaultTtl
     */
    public function testSetDefaultTtlOnInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('TTL must be an integer (seconds)');
        $this->cfg->setDefaultTtl('foo');
    }

    /**
     * @covers ::setPrefix
     * @covers ::prefix
     */
    public function testPrefix()
    {
        // Chainable
        $that = $this->cfg->setPrefix('foo');
        $this->assertSame($this->cfg, $that);

        // Mutated State
        $this->assertEquals('foo', $this->cfg->prefix());
    }

    /**
     * @covers ::setPrefix
     */
    public function testSetPrefixOnInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Prefix must be a string');
        $this->cfg->setPrefix(false);
    }

    /**
     * @covers ::setPrefix
     */
    public function testSetPrefixOnInvalidValue()
    {
        $this->expectExceptionMessage('Prefix must be alphanumeric');
        $this->expectException(InvalidArgumentException::class);
        $this->cfg->setPrefix('foo!#$bar');
    }
}
