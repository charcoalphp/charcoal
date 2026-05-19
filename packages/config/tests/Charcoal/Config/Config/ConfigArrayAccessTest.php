<?php

namespace Charcoal\Tests\Config\Config;

use ArrayAccess;

// From 'charcoal-config'
use Charcoal\Tests\Config\Config\AbstractConfigTestCase;
use Charcoal\Tests\Config\Mixin\ArrayAccessTestTrait;
use Charcoal\Tests\Config\Mock\MacroConfig;
use Charcoal\Config\AbstractConfig;

/**
 * Test ArrayAccess implementation in AbstractConfig
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Config\AbstractConfig::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'offsetExists()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'offsetGet()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'offsetSet()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'offsetUnset()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Tests\Config\Mock\MacroConfig::class, 'foo()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Tests\Config\Mock\MacroConfig::class, 'setFoo()')]
class ConfigArrayAccessTest extends AbstractConfigTestCase
{
    use ArrayAccessTestTrait;

    /**
     * @var MacroConfig
     */
    public $cfg;

    /**
     * Create a concrete MacroConfig instance.
     */
    protected function setUp(): void
    {
        $this->cfg = $this->createConfig([
            'name' => 'Charcoal',
            'foo'  => 10,
            'erd'  => true,
        ]);
    }

    /**
     * Asserts that the object implements ArrayAccess.
     *
     * @return MacroConfig
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testArrayAccess()
    {
        $this->assertInstanceOf(ArrayAccess::class, $this->cfg);
        return $this->cfg;
    }



    // Test ArrayAccess on non-private properties
    // =========================================================================
    public function testOffsetExists(): void
    {
        $cfg = $this->cfg;

        // MacroConfig::$name
        $this->assertTrue(property_exists($cfg, 'name'));
        $this->assertTrue(isset($cfg['name']));

        // MacroConfig::foo()
        $this->assertTrue(isset($cfg['foo']));

        // MacroConfig::getErd()
        $this->assertTrue(isset($cfg['erd']));
    }

    public function testOffsetGet(): void
    {
        $cfg = $this->cfg;

        // MacroConfig::$name
        $this->assertEquals('Charcoal', $cfg['name']);

        // MacroConfig::foo()
        $this->assertEquals('foo is 20', $cfg['foo']);

        // MacroConfig::getErd()
        $this->assertEquals(true, $cfg['erd']);
    }

    public function testOffsetSet(): void
    {
        $cfg = $this->cfg;

        $cfg['baz'] = 'waldo';
        $this->assertTrue(property_exists($cfg, 'baz'));
        $this->assertEquals('waldo', $cfg['baz']);
    }

    public function testOffsetUnset(): void
    {
        $cfg = $this->cfg;

        unset($cfg['name']);
        $this->assertTrue(property_exists($cfg, 'name'));
        $this->assertNull($cfg['name']);
    }



    // Test ArrayAccess on encapsulated properties
    // =========================================================================
    public function testOffsetExistsOnEncapsulatedMethod(): void
    {
        $cfg = $this->cfg;

        $this->assertTrue(property_exists($cfg, 'foo'));
        $this->assertTrue(isset($cfg['foo']));
    }

    public function testOffsetGetOnEncapsulatedMethod(): void
    {
        $cfg = $this->cfg;

        $this->assertEquals('foo is 20', $cfg['foo']);
    }

    public function testOffsetSetOnEncapsulatedMethod(): void
    {
        $cfg = $this->cfg;

        $cfg['foo'] = 32;
        $this->assertEquals('foo is 42', $cfg['foo']);
    }

    public function testOffsetUnsetOnEncapsulatedMethod(): void
    {
        $cfg = $this->cfg;

        unset($cfg['foo']);
        $this->assertTrue(property_exists($cfg, 'foo'));
        $this->assertEquals('foo is 10', $cfg['foo']);
    }
}
