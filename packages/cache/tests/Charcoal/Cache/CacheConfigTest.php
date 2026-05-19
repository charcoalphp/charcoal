<?php

namespace Charcoal\Tests\Cache;

use InvalidArgumentException;

// From 'charcoal-cache'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Cache\CacheConfig;

/**
 * Test CacheConfig
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Cache\CacheConfig::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'defaults')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'active')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'types')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'defaultTypes')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'defaultTtl')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'prefix')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'setActive')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'setTypes')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'addTypes')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'addType')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'validTypes')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'setDefaultTtl')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheConfig::class, 'setPrefix')]
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
     */
    public function configFactory(array $args = []): \Charcoal\Cache\CacheConfig
    {
        return new CacheConfig($args);
    }

    public function testDefaults(): void
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

    public function testActive(): void
    {
        // Chainable
        $that = $this->cfg->setActive(false);
        $this->assertSame($this->cfg, $that);

        // Mutated State
        $this->assertFalse($this->cfg->active());
    }

    public function testReplaceDrivers(): void
    {
        // Chainable
        $that = $this->cfg->setTypes([ 'memcache', 'noop' ]);
        $this->assertSame($this->cfg, $that);

        // Mutated State
        $types = $this->cfg->types();
        $this->assertEquals([ 'memcache', 'noop', 'memory' ], $types);
    }

    public function testUniqueDrivers(): void
    {
        $this->cfg->setTypes([ 'memcache', 'memory', 'file', 'memcache' ]);

        $types = $this->cfg->types();
        $this->assertEquals([ 'memcache', 'memory', 'file' ], $types);
    }

    public function testAddDrivers(): void
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

    public function testAddDriverOnInvalidType(): void
    {
        $this->expectExceptionMessage('Invalid cache type: "foobar"');
        $this->expectException(InvalidArgumentException::class);
        $this->cfg->addType('foobar');
    }

    public function testDefaultTtl(): void
    {
        // Chainable
        $that = $this->cfg->setDefaultTtl(42);
        $this->assertSame($this->cfg, $that);

        // Mutated State
        $this->assertEquals(42, $this->cfg->defaultTtl());
    }

    public function testSetDefaultTtlOnInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('TTL must be an integer (seconds)');
        $this->cfg->setDefaultTtl('foo');
    }

    public function testPrefix(): void
    {
        // Chainable
        $that = $this->cfg->setPrefix('foo');
        $this->assertSame($this->cfg, $that);

        // Mutated State
        $this->assertEquals('foo', $this->cfg->prefix());
    }

    public function testSetPrefixOnInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Prefix must be a string');
        $this->cfg->setPrefix(false);
    }

    public function testSetPrefixOnInvalidValue(): void
    {
        $this->expectExceptionMessage('Prefix must be alphanumeric');
        $this->expectException(InvalidArgumentException::class);
        $this->cfg->setPrefix('foo!#$bar');
    }
}
