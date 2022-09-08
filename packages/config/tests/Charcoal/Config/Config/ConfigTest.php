<?php

namespace Charcoal\Tests\Config\Config;

use Charcoal\Tests\AssertionsTrait;
use StdClass;
use ArrayIterator;
use IteratorAggregate;
use InvalidArgumentException;

// From PSR-11
use Psr\Container\ContainerInterface;

// From 'charcoal-config'
use Charcoal\Tests\Config\Config\AbstractConfigTestCase;
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
class ConfigTest extends AbstractConfigTestCase
{
    use AssertionsTrait;

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
        $this->cfg = $this->createConfig();
    }

    /**
     * Asserts that the object implements PSR-11.
     *
     * @coversNothing
     * @return void
     */
    public function testPsr11()
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->cfg);
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
        $this->assertInstanceOf(ArrayIterator::class, $this->cfg->getIterator());
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
     * @covers ::__construct
     * @covers ::merge
     * @return void
     */
    public function testConstructWithTraversableInstance()
    {
        $iter = new ArrayIterator([
            'name' => 'Charcoal'
        ]);
        $cfg  = $this->mockConfig($iter);
        $this->assertEquals('Charcoal', $cfg['name']);
    }

    /**
     *
     * @covers ::__construct
     * @covers ::merge
     * @return void
     */
    public function testConstructWithInvalidData()
    {
        $this->expectExceptionMessage('Data must be a config file, an associative array, or an object implementing Traversable');
        $this->expectException(InvalidArgumentException::class);

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
