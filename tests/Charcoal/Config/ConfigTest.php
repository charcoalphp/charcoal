<?php

namespace Charcoal\Tests\Config;

use StdClass;
use Iterator;
use IteratorAggregate;
use InvalidArgumentException;

// From 'charcoal-config'
use Charcoal\Tests\Config\AbstractConfigTest;
use Charcoal\Tests\Config\Mock\MacroConfig;
use Charcoal\Config\AbstractConfig;

/**
 * Test AbstractConfig
 *
 * Decoupled Tests:
 * - ConfigArrayAccessTest
 * - ConfigArrayMergeTest
 * - ConfigDelegatesAwareTest
 * - ConfigSeparatorAwareTest
 * - ConfigFileAwareTest
 * - FileLoader/*
 *
 * @coversDefaultClass \Charcoal\Config\AbstractConfig
 */
class ConfigTest extends AbstractConfigTest
{
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
        $this->cfg = $this->createConfig();
    }

    /**
     * Asserts that the object implements IteratorAggregate.
     *
     * @covers ::getIterator()
     * @return void
     */
    public function testIteratorAggregate()
    {
        $this->assertInstanceOf(IteratorAggregate::class, $this->cfg);
        $this->assertInstanceOf(Iterator::class, $this->cfg->getIterator());
    }

    /**
     * @covers ::__construct
     * @covers ::merge
     * @return void
     */
    public function testConstructWithArray()
    {
        $cfg = $this->mockConfig([
            'name' => 'Charcoal'
        ]);
        $this->assertEquals('Charcoal', $cfg['name']);
    }

    /**
     * @covers ::__construct
     * @covers ::merge
     * @return void
     */
    public function testConstructWithConfigInstance()
    {
        $cfg = $this->mockConfig($this->cfg);
        $this->assertEquals('garply', $cfg['baz']);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Data must be an associative array, a file path,
     *     or an instance of Charcoal\Config\ConfigInterface
     *
     * @covers ::__construct
     * @covers ::merge
     * @return void
     */
    public function testConstructWithInvalidData()
    {
        $std = new StdClass;
        $cfg = $this->mockConfig($std);
    }



    // Test Defaults
    // =========================================================================

    /**
     * Asserts that, when defined, a Config will apply the class' default data.
     *
     * @covers ::__construct
     * @covers ::setData
     * @covers ::defaults
     * @return void
     */
    public function testConstructWithDefaults()
    {
        /** @var array $defaults {@see \Charcoal\Tests\Config\Mock\MacroConfig::defaults()} */
        $defaults = [
            'foo' => -3,
            'baz' => 'garply',
            'erd' => true,
        ];

        $initial = [
            'foo' => 'foo is 7',
            'baz' => 'garply',
            'erd' => true,
        ];

        $cfg = $this->cfg;
        $this->assertArraySubsets($defaults, $cfg->defaults(), true, 'Test defaults');
        $this->assertArraySubsets($initial, $cfg->data(), true, 'Test initial data against defaults');

        $changes = [
            'baz' => 'waldo',
            'erd' => false,
        ];

        $mutated = [
            'foo' => 'foo is 7',
            'baz' => 'waldo',
            'erd' => false,
        ];

        $cfg->setData($changes);
        $this->assertArraySubsets($mutated, $cfg->data(), true, 'Test mutated data against defaults');
    }

    /**
     * Asserts that, by default, a Config has no default data.
     *
     * @covers ::defaults
     * @return void
     */
    public function testEmptyDefaults()
    {
        $cfg = $this->mockConfig();
        $this->assertEmpty($cfg->defaults());
        $this->assertEmpty($cfg->keys());
    }
}
