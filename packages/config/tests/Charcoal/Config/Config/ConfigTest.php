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
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Config\AbstractConfig::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'getIterator()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, '__construct')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'merge')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'setData')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractConfig::class, 'defaults')]
class ConfigTest extends AbstractConfigTestCase
{
    use AssertionsTrait;

    /**
     * @var MacroConfig
     */
    public $cfg;

    /**
     * Create a concrete MacroConfig instance.
     */
    protected function setUp(): void
    {
        $this->cfg = $this->createConfig();
    }

    /**
     * Asserts that the object implements PSR-11.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testPsr11(): void
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->cfg);
    }

    /**
     * Asserts that the object implements IteratorAggregate.
     */
    public function testIteratorAggregate(): void
    {
        $this->assertInstanceOf(IteratorAggregate::class, $this->cfg);
        $this->assertInstanceOf(ArrayIterator::class, $this->cfg->getIterator());
    }

    public function testConstructWithArray(): void
    {
        $cfg = $this->mockConfig([
            'name' => 'Charcoal'
        ]);
        $this->assertEquals('Charcoal', $cfg['name']);
    }

    public function testConstructWithConfigInstance(): void
    {
        $cfg = $this->mockConfig($this->cfg);
        $this->assertEquals('garply', $cfg['baz']);
    }

    public function testConstructWithTraversableInstance(): void
    {
        $iter = new ArrayIterator([
            'name' => 'Charcoal'
        ]);
        $cfg  = $this->mockConfig($iter);
        $this->assertEquals('Charcoal', $cfg['name']);
    }

    public function testConstructWithInvalidData(): void
    {
        $this->expectExceptionMessage('Data must be a config file, an associative array, or an object implementing Traversable');
        $this->expectException(InvalidArgumentException::class);

        $std = new StdClass;
        $this->mockConfig($std);
    }



    // Test Defaults
    // =========================================================================
    /**
     * Asserts that, when defined, a Config will apply the class' default data.
     */
    public function testConstructWithDefaults(): void
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
     */
    public function testEmptyDefaults(): void
    {
        $cfg = $this->mockConfig();
        $this->assertEmpty($cfg->defaults());
        $this->assertEmpty($cfg->keys());
    }
}
