<?php

namespace Charcoal\Tests\Config\Config;

// From 'charcoal-config'
use Charcoal\Tests\Config\Config\AbstractConfigTestCase;
use Charcoal\Tests\Config\Mock\MacroConfig;
use Charcoal\Config\AbstractConfig;
use Charcoal\Config\DelegatesAwareInterface;

/**
 * Test DelegatesAwareTrait implementation in AbstractConfig
 *
 * @coversDefaultClass \Charcoal\Config\AbstractConfig
 */
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
     *
     * @return void
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
     *
     * @coversNothing
     * @return void
     */
    public function testDelegatesAwareInterface()
    {
        $this->assertInstanceOf(DelegatesAwareInterface::class, $this->cfg);
    }



    // Test Delegate Collecting
    // =========================================================================

    /**
     * @covers ::__construct()
     * @covers ::setDelegates()
     * @covers ::addDelegate()
     * @covers ::prependDelegate()
     * @return void
     */
    public function testSetDelegates()
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
     *
     * @covers ::offsetExists()
     * @return void
     */
    public function testOffsetExistsInDelegates()
    {
        $cfg = $this->cfg;

        $this->assertObjectNotHasAttribute('bar', $cfg);
        $this->assertObjectHasAttribute('bar', $this->delegates[1]);
        $this->assertTrue(isset($cfg['bar']));
    }

    /**
     * Asserts that the delegate container returns FALSE if a data key is nonexistent
     * {@see DelegatesAwareTrait::hasInDelegates() among its delegates}.
     *
     * @covers ::offsetExists()
     * @return void
     */
    public function testOffsetExistsReturnsFalseOnNonexistentKeyInDelegates()
    {
        $cfg = $this->cfg;

        $this->assertObjectNotHasAttribute('zyx', $cfg);
        $this->assertFalse(isset($cfg['zyx']));
    }

    /**
     * Asserts that the delegate container returns the value of a data key found
     * {@see DelegatesAwareTrait::getInDelegates() among its delegates}.
     *
     * @covers ::offsetGet()
     * @return void
     */
    public function testOffsetGetInDelegates()
    {
        $cfg = $this->cfg;

        $this->assertObjectNotHasAttribute('qux', $cfg);
        $this->assertObjectHasAttribute('qux', $this->delegates[2]);
        $this->assertEquals($this->delegates[2]['qux'], $cfg['qux']);
    }

    /**
     * Asserts that the delegate container returns NULL if a data key is nonexistent
     * {@see DelegatesAwareTrait::getInDelegates() among its delegates}.
     *
     * @covers ::offsetExists()
     * @return void
     */
    public function testOffsetGetReturnsNullOnNonexistentKeyInDelegates()
    {
        $cfg = $this->cfg;

        $this->assertObjectNotHasAttribute('xyz', $cfg);
        $this->assertNull($cfg['xyz']);
    }

    /**
     * Asserts that attributes in delegates cannot be mutated by the delegate container.
     *
     * @coversNothing
     * @return void
     */
    public function testOffsetSetDoesNotPerformMutationsInDelegates()
    {
        $cfg = $this->cfg;

        $this->assertObjectNotHasAttribute('qux', $cfg);
        $this->assertObjectHasAttribute('qux', $this->delegates[2]);

        $cfg['qux'] = 'garply';
        $this->assertObjectHasAttribute('qux', $cfg);
        $this->assertEquals('garply', $cfg['qux']);
        $this->assertEquals('xyzzy', $this->delegates[2]['qux']);
    }

    /**
     * Asserts that attributes in delegates cannot be removed by the delegate container.
     *
     * @coversNothing
     * @return void
     */
    public function testOffsetUnsetDoesNotPerformMutationsInDelegates()
    {
        $cfg = $this->cfg;

        $this->assertObjectNotHasAttribute('qux', $cfg);
        $this->assertObjectHasAttribute('qux', $this->delegates[2]);

        unset($cfg['qux']);
        $this->assertEquals($this->delegates[2]['qux'], $cfg['qux']);
    }

    /**
     * Asserts that removing a value from the delegate container allows subsequent requests
     * to lookup a fallback in a delegate.
     *
     * @coversNothing
     * @return void
     */
    public function testOffsetUnsetOnConfigWithFallbackInDelegates()
    {
        $cfg = $this->cfg;

        $this->assertObjectHasAttribute('hud', $cfg);
        $this->assertEquals('flob', $cfg['hud']);

        unset($cfg['hud']);
        $this->assertEquals($this->delegates[0]['hud'], $cfg['hud']);
    }
}
