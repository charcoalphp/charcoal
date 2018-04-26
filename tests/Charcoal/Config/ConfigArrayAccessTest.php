<?php

namespace Charcoal\Tests\Config;

use ArrayAccess;

// From 'charcoal-config'
use Charcoal\Tests\ArrayAccessTrait;
use Charcoal\Tests\Config\AbstractConfigTest;
use Charcoal\Tests\Config\Mock\MacroConfig;
use Charcoal\Config\AbstractConfig;

/**
 * Test ArrayAccess implementation in AbstractConfig
 *
 * @coversDefaultClass \Charcoal\Config\AbstractConfig
 */
class ConfigArrayAccessTest extends AbstractConfigTest
{
    use ArrayAccessTrait;

    /**
     * @var MacroConfig
     */
    public $cfg;

    /**
     * Create a concrete MacroConfig instance.
     *
     * @return void
     */
    public function setUp()
    {
        $this->cfg = $this->createConfig([
            'name' => 'Charcoal',
            'foo'  => 10,
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

    /**
     * @covers ::offsetExists()
     * @return void
     */
    public function testOffsetExists()
    {
        $cfg = $this->cfg;

        $this->assertObjectHasAttribute('name', $cfg);
        $this->assertTrue(isset($cfg['name']));
    }

    /**
     * @covers ::offsetGet()
     * @return void
     */
    public function testOffsetGet()
    {
        $cfg = $this->cfg;

        $this->assertAttributeEquals('Charcoal', 'name', $cfg);
        $this->assertEquals('Charcoal', $cfg['name']);
    }

    /**
     * @covers ::offsetSet()
     * @return void
     */
    public function testOffsetSet()
    {
        $cfg = $this->cfg;

        $cfg['baz'] = 'waldo';
        $this->assertObjectHasAttribute('baz', $cfg);
        $this->assertAttributeEquals('waldo', 'baz', $cfg);
        $this->assertEquals('waldo', $cfg['baz']);
    }

    /**
     * @covers ::offsetUnset()
     * @return void
     */
    public function testOffsetUnset()
    {
        $cfg = $this->cfg;

        unset($cfg['name']);
        $this->assertObjectHasAttribute('name', $cfg);
        $this->assertAttributeEmpty('name', $cfg);
        $this->assertNull($cfg['name']);
    }



    // Test ArrayAccess on encapsulated properties
    // =========================================================================

    /**
     * @covers \Charcoal\Tests\Config\Mock\MacroConfig::foo()
     * @covers ::offsetExists()
     * @return void
     */
    public function testOffsetExistsOnEncapsulatedMethod()
    {
        $cfg = $this->cfg;

        $this->assertObjectHasAttribute('foo', $cfg);
        $this->assertTrue(isset($cfg['foo']));
    }

    /**
     * @covers \Charcoal\Tests\Config\Mock\MacroConfig::foo()
     * @covers ::offsetGet()
     * @return void
     */
    public function testOffsetGetOnEncapsulatedMethod()
    {
        $cfg = $this->cfg;

        $this->assertAttributeEquals(20, 'foo', $cfg);
        $this->assertEquals('foo is 20', $cfg['foo']);
    }

    /**
     * @covers \Charcoal\Tests\Config\Mock\MacroConfig::setFoo()
     * @covers ::offsetSet()
     * @return void
     */
    public function testOffsetSetOnEncapsulatedMethod()
    {
        $cfg = $this->cfg;

        $cfg['foo'] = 32;
        $this->assertAttributeEquals(42, 'foo', $cfg);
        $this->assertEquals('foo is 42', $cfg['foo']);
    }

    /**
     * @covers \Charcoal\Tests\Config\Mock\MacroConfig::setFoo()
     * @covers ::offsetUnset()
     * @return void
     */
    public function testOffsetUnsetOnEncapsulatedMethod()
    {
        $cfg = $this->cfg;

        unset($cfg['foo']);
        $this->assertObjectHasAttribute('foo', $cfg);
        $this->assertAttributeEquals(10, 'foo', $cfg);
        $this->assertEquals('foo is 10', $cfg['foo']);
    }
}
