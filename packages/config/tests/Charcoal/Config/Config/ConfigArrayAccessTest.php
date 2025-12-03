<?php

namespace Charcoal\Tests\Config\Config;

use ArrayAccess;
// From 'charcoal-config'
use Charcoal\Tests\Config\Config\AbstractConfigTestCase;
use Charcoal\Tests\Config\Mixin\ArrayAccessTestTrait;
use Charcoal\Tests\Config\Mock\MacroConfig;
use Charcoal\Config\AbstractConfig;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * Test ArrayAccess implementation in AbstractConfig
 */
#[CoversMethod(AbstractConfig::class, 'offsetExists')]
#[CoversMethod(AbstractConfig::class, 'offsetGet')]
#[CoversMethod(AbstractConfig::class, 'offsetSet')]
#[CoversMethod(AbstractConfig::class, 'offsetUnset')]
class ConfigArrayAccessTest extends AbstractConfigTestCase
{
    use ArrayAccessTestTrait;

    /**
     * @var MacroConfig
     */
    public $cfg;

    /**
     * Create a concrete MacroConfig instance.
     *
     * @return void
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
     * @coversNothing
     * @return MacroConfig
     */
    public function testArrayAccess()
    {
        $this->assertInstanceOf(ArrayAccess::class, $this->cfg);
        return $this->cfg;
    }



    // Test ArrayAccess on non-private properties
    // =========================================================================

    public function testOffsetExists()
    {
        $cfg = $this->cfg;

        // MacroConfig::$name
        $this->assertObjectHasProperty('name', $cfg);
        $this->assertTrue(isset($cfg['name']));

        // MacroConfig::foo()
        $this->assertTrue(isset($cfg['foo']));

        // MacroConfig::getErd()
        $this->assertTrue(isset($cfg['erd']));
    }

    public function testOffsetGet()
    {
        $cfg = $this->cfg;

        // MacroConfig::$name
        $this->assertEquals('Charcoal', $cfg['name']);

        // MacroConfig::foo()
        $this->assertEquals('foo is 20', $cfg['foo']);

        // MacroConfig::getErd()
        $this->assertEquals(true, $cfg['erd']);
    }

    public function testOffsetSet()
    {
        $cfg = $this->cfg;

        $cfg['baz'] = 'waldo';
        $this->assertObjectHasProperty('baz', $cfg);
        $this->assertEquals('waldo', $cfg['baz']);
    }

    public function testOffsetUnset()
    {
        $cfg = $this->cfg;

        unset($cfg['name']);
        $this->assertObjectHasProperty('name', $cfg);
        $this->assertNull($cfg['name']);
    }



    // Test ArrayAccess on encapsulated properties
    // =========================================================================

    public function testOffsetExistsOnEncapsulatedMethod()
    {
        $cfg = $this->cfg;

        $this->assertObjectHasProperty('foo', $cfg);
        $this->assertTrue(isset($cfg['foo']));
    }

    public function testOffsetGetOnEncapsulatedMethod()
    {
        $cfg = $this->cfg;

        $this->assertEquals('foo is 20', $cfg['foo']);
    }

    public function testOffsetSetOnEncapsulatedMethod()
    {
        $cfg = $this->cfg;

        $cfg['foo'] = 32;
        $this->assertEquals('foo is 42', $cfg['foo']);
    }

    public function testOffsetUnsetOnEncapsulatedMethod()
    {
        $cfg = $this->cfg;

        unset($cfg['foo']);
        $this->assertObjectHasProperty('foo', $cfg);
        $this->assertEquals('foo is 10', $cfg['foo']);
    }
}
