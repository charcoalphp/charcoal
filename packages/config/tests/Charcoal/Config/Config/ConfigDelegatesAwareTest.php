<?php

namespace Charcoal\Tests\Config\Config;

// From 'charcoal-config'
use Charcoal\Tests\Config\Config\AbstractConfigTestCase;
use Charcoal\Tests\Config\Mock\MacroConfig;
use Charcoal\Config\AbstractConfig;
use Charcoal\Config\DelegatesAwareInterface;

/**
 * Test DelegatesAwareTrait implementation in AbstractConfig
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Config\AbstractConfig::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, '__construct()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'setDelegates()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'addDelegate()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'prependDelegate()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'offsetExists()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'offsetGet()')]
class ConfigDelegatesAwareTest extends AbstractConfigTestCase
{
    /**
     * @var MacroConfig
     */
    public $cfg;

    /**
     * @var MacroConfig[]
     */
    public $delegates;

    /**
     * Create a concrete MacroConfig instance.
     */
    protected function setUp(): void
    {
        $this->delegates = [
            $this->createConfig([
                'uid' => '4d5e',
                'foo' => 20,
                'bop' => 0,
                'hud' => 'blep',
            ]),
            $this->createConfig([
                'uid' => '813d',
                'foo' => 30,
                'bop' => 1,
                'bar' => true,
            ]),
            $this->createConfig([
                'uid' => 'a379',
                'foo' => 40,
                'bop' => 2,
                'qux' => 'xyzzy',
            ]),
        ];

        $this->cfg = $this->createConfig([
            'uid' => '929d',
            'foo' => 10,
            'hud' => 'flob',
        ], $this->delegates);
    }

    /**
     * Asserts that the object implements DelegatesAwareInterface.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testDelegatesAwareInterface(): void
    {
        $this->assertInstanceOf(DelegatesAwareInterface::class, $this->cfg);
    }



    // Test Delegate Collecting
    // =========================================================================
    public function testSetDelegates(): void
    {
        $cfg = $this->createConfig(null, [ $this->delegates[0] ]);
        $this->assertEquals(0, $cfg['bop']);

        $cfg->addDelegate($this->delegates[1]);
        $this->assertNotEquals(1, $cfg['bop']);

        $cfg->prependDelegate($this->delegates[2]);
        $this->assertEquals(2, $cfg['bop']);
    }



    // Test ArrayAccess on delegated properties
    // =========================================================================
    /**
     * Asserts that the delegate container returns TRUE if a data key is found
     * {@see DelegatesAwareTrait::hasInDelegates() among its delegates}.
     */
    public function testOffsetExistsInDelegates(): void
    {
        $cfg = $this->cfg;

        $this->assertFalse(property_exists($cfg, 'bar'));
        $this->assertTrue(property_exists($this->delegates[1], 'bar'));
        $this->assertTrue(isset($cfg['bar']));
    }

    /**
     * Asserts that the delegate container returns FALSE if a data key is nonexistent
     * {@see DelegatesAwareTrait::hasInDelegates() among its delegates}.
     */
    public function testOffsetExistsReturnsFalseOnNonexistentKeyInDelegates(): void
    {
        $cfg = $this->cfg;

        $this->assertFalse(property_exists($cfg, 'zyx'));
        $this->assertFalse(isset($cfg['zyx']));
    }

    /**
     * Asserts that the delegate container returns the value of a data key found
     * {@see DelegatesAwareTrait::getInDelegates() among its delegates}.
     */
    public function testOffsetGetInDelegates(): void
    {
        $cfg = $this->cfg;

        $this->assertFalse(property_exists($cfg, 'qux'));
        $this->assertTrue(property_exists($this->delegates[2], 'qux'));
        $this->assertEquals($this->delegates[2]['qux'], $cfg['qux']);
    }

    /**
     * Asserts that the delegate container returns NULL if a data key is nonexistent
     * {@see DelegatesAwareTrait::getInDelegates() among its delegates}.
     */
    public function testOffsetGetReturnsNullOnNonexistentKeyInDelegates(): void
    {
        $cfg = $this->cfg;

        $this->assertFalse(property_exists($cfg, 'xyz'));
        $this->assertNull($cfg['xyz']);
    }

    /**
     * Asserts that attributes in delegates cannot be mutated by the delegate container.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testOffsetSetDoesNotPerformMutationsInDelegates(): void
    {
        $cfg = $this->cfg;

        $this->assertFalse(property_exists($cfg, 'qux'));
        $this->assertTrue(property_exists($this->delegates[2], 'qux'));

        $cfg['qux'] = 'garply';
        $this->assertTrue(property_exists($cfg, 'qux'));
        $this->assertEquals('garply', $cfg['qux']);
        $this->assertEquals('xyzzy', $this->delegates[2]['qux']);
    }

    /**
     * Asserts that attributes in delegates cannot be removed by the delegate container.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testOffsetUnsetDoesNotPerformMutationsInDelegates(): void
    {
        $cfg = $this->cfg;

        $this->assertFalse(property_exists($cfg, 'qux'));
        $this->assertTrue(property_exists($this->delegates[2], 'qux'));

        unset($cfg['qux']);
        $this->assertEquals($this->delegates[2]['qux'], $cfg['qux']);
    }

    /**
     * Asserts that removing a value from the delegate container allows subsequent requests
     * to lookup a fallback in a delegate.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testOffsetUnsetOnConfigWithFallbackInDelegates(): void
    {
        $cfg = $this->cfg;

        $this->assertTrue(property_exists($cfg, 'hud'));
        $this->assertEquals('flob', $cfg['hud']);

        unset($cfg['hud']);
        $this->assertEquals($this->delegates[0]['hud'], $cfg['hud']);
    }
}
