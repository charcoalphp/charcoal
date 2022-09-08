<?php

namespace Charcoal\Tests\Config\Config;

use StdClass;
use Iterator;
use IteratorAggregate;
use InvalidArgumentException;

// From 'charcoal-config'
use Charcoal\Tests\Config\Config\AbstractConfigTestCase;
use Charcoal\Config\GenericConfig;
use Charcoal\Tests\AssertionsTrait;

/**
 * Test data merging in AbstractConfig
 *
 * @coversDefaultClass \Charcoal\Config\AbstractConfig
 */
class ConfigArrayMergeTest extends AbstractConfigTestCase
{
    use AssertionsTrait;

    /**
     * @var GenericConfig
     */
    public $cfg;

    /**
     * Create a concrete GenericConfig instance.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->cfg = $this->createConfig();
    }

    /**
     * Create a GenericConfig instance.
     *
     * @param  mixed $data      Data to pre-populate the object.
     * @param  array $delegates Delegates to pre-populate the object.
     * @return GenericConfig
     */
    public function createConfig($data = null, array $delegates = null)
    {
        return new GenericConfig($data, $delegates);
    }

    // =========================================================================

    /**
     * Test {@see AbstractEntity::merge()} with array.
     *
     * @covers ::offsetReplace()
     * @covers ::merge()
     * @return void
     */
    public function testMergeDataWithArray()
    {
        $cfg = $this->cfg;

        $initial  = $this->getInitialConfigData();
        $mutated  = $this->getMutatedConfigData();
        $expected = $this->getExpectedConfigData();

        $cfg->setData($initial);
        $that = $cfg->merge($mutated);
        $this->assertSame($cfg, $that);

        $this->assertEquals($expected, $cfg->data());
    }

    /**
     * Test {@see AbstractEntity::merge()} with another Config instance.
     *
     * @covers ::offsetReplace()
     * @covers ::merge()
     * @return void
     */
    public function testMergeDataWithConfigInstance()
    {
        $cfg = $this->cfg;

        $initial  = $this->getInitialConfigData();
        $mutated  = $this->createConfig($this->getMutatedConfigData());
        $expected = $this->getExpectedConfigData();

        $cfg->setData($initial);
        $cfg->merge($mutated);

        $this->assertEquals($expected, $cfg->data());
    }

    /**
     * Gets the intiial Config data.
     *
     * @return array
     */
    public function getInitialConfigData()
    {
        return [
            'name'     => 'vendor/my-cool-app',
            'keywords' => [ 'charcoal', 'framework' ],
            'require'  => [
                'vendor/my-lib' => '^1.0',
            ],
            'repositories' => [
                [
                    'url' => 'git://example.tld',
                ],
            ],
        ];
    }

    /**
     * Gets the mutations for the Config.
     *
     * @return array
     */
    public function getMutatedConfigData()
    {
        return [
            'name'     => 'vendor/my-awesome-app',
            'keywords' => [ 'database', 'modeling', 'templating' ],
            'require'  => [
                'vendor/my-lib'       => '^2.0',
                'vendor/my-other-lib' => '^2.0',
            ],
            'repositories' => [
                [
                    'url' => 'https://pear2.php.net',
                ],
            ],
        ];
    }

    /**
     * Gets the expected Config data.
     *
     * @return array
     */
    public function getExpectedConfigData()
    {
        return [
            'name'     => 'vendor/my-awesome-app',
            'keywords' => [ 'database', 'modeling', 'templating' ],
            'require'  => [
                'vendor/my-lib'       => '^2.0',
                'vendor/my-other-lib' => '^2.0',
            ],
            'repositories' => [
                [
                    'url' => 'https://pear2.php.net',
                ],
            ],
        ];
    }

    // =========================================================================

    /**
     * Asserts that the container assigns a value to the endpoint
     * {@see SeparatorAwareTrait::setWithSeparator() of the keypath}.
     *
     * @covers ::offsetReplace()
     * @return void
     */
    public function testOffsetMergeOnEndKeyPath()
    {
        $cfg = $this->cfg;

        $cfg->setData([
            'repository' => $this->getInitialConfigData()
        ]);

        $cfg->offsetReplace('repository.require.vendor/my-other-lib', '^2.0');
        $this->assertArraySubsets([
            'vendor/my-lib'       => '^1.0',
            'vendor/my-other-lib' => '^2.0',
        ], $cfg['repository.require']);
    }

    /**
     * Asserts that the container assigns a value to the endpoint of a nonexistent midpoint
     * {@see SeparatorAwareTrait::setWithSeparator() in the keypath}.
     *
     * @covers ::offsetReplace()
     * @return void
     */
    public function testOffsetMergeOnNonexistentMidKeyPath()
    {
        $cfg = $this->cfg;

        $cfg->setData([
            'repository' => $this->getInitialConfigData()
        ]);

        $this->assertNull($cfg['repository.require-dev']);

        $cfg->offsetReplace('repository.require-dev.my-vend/my-lib-tests', '1.*');
        $this->assertArraySubset([
            'my-vend/my-lib-tests' => '1.*'
        ], $cfg['repository.require-dev']);
    }

    // =========================================================================

    /**
     * @covers ::offsetReplace()
     * @return void
     */
    public function testOffsetMergeIgnoredOnZeroLengthKey()
    {
        $this->cfg->offsetReplace('', 'waldo');
        $this->assertNull($this->cfg['']);
    }

    /**
     * @covers ::offsetReplace()
     * @return void
     */
    public function testOffsetMergeIgnoredOnUnderscoreKey()
    {
        $this->cfg->offsetReplace('_', 'waldo');
        $this->assertNull($this->cfg['_']);
    }

    /**
     * Asserts that a numeric key throws an exception, when merging a value.
     *
     * @covers ::offsetReplace()
     * @return void
     */
    public function testOffsetMergeThrowsExceptionOnNumericKey()
    {
        $this->expectExceptionMessage("Entity array access only supports non-numeric keys");
        $this->expectException(InvalidArgumentException::class);
        $this->cfg->offsetReplace(0, 'waldo');
    }
}
