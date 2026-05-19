<?php

namespace Charcoal\Tests\Config\Mixin;

// From 'charcoal-config'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\AssertionsTrait;
use Charcoal\Tests\FixturesTrait;
use Charcoal\Tests\Config\Mock\ConfigurableObject;
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;
use Charcoal\Config\ConfigInterface;
use Charcoal\Config\GenericConfig;
use InvalidArgumentException;

/**
 * Test ConfigurableTrait
 */
#[\PHPUnit\Framework\Attributes\CoversTrait(\Charcoal\Config\ConfigurableTrait::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\ConfigurableTrait::class, 'createConfig()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\ConfigurableTrait::class, 'setConfig()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\ConfigurableTrait::class, 'config()')]
class ConfigurableTest extends AbstractTestCase
{
    use AssertionsTrait;
    use FixturesTrait;

    /**
     * @var ConfigurableObject
     */
    public $obj;

    /**
     * @var GenericConfig
     */
    public $cfg;

    /**
     * @var array
     */
    public $data;

    /**
     * Create a ConfigurableObject instance.
     */
    protected function setUp(): void
    {
        $this->data = [
            'name' => 'mydb',
            'user' => 'myname',
            'pass' => 'secret',
        ];

        $this->cfg = $this->createConfig($this->data);

        $this->obj = $this->createObject();
    }

    /**
     * Create a ConfigurableObject instance.
     */
    public function createObject(): \Charcoal\Tests\Config\Mock\ConfigurableObject
    {
        return new ConfigurableObject();
    }

    /**
     * Create a GenericConfig instance.
     *
     * @param  mixed $data      Data to pre-populate the object.
     * @param  array $delegates Delegates to pre-populate the object.
     */
    public function createConfig($data = null, ?array $delegates = null): \Charcoal\Config\GenericConfig
    {
        return new GenericConfig($data, $delegates);
    }

    /**
     * Asserts that the object implements ConfigurableInterface.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testConfigurableInterface(): void
    {
        $this->assertInstanceOf(ConfigurableInterface::class, $this->obj);
        $this->assertInstanceOf(ConfigInterface::class, $this->obj->createConfig());
    }


    // Test SetConfig
    // =========================================================================
    public function testSetConfigWithString(): void
    {
        $path = $this->getPathToFixture('pass/valid.json');
        $that = $this->obj->setConfig($path);
        $this->assertSame($this->obj, $that);
        $this->assertInstanceOf(GenericConfig::class, $this->obj->config());

        $cfg = $this->obj->config();
        $this->assertJsonStringEqualsJsonFile($path, json_encode($cfg));
    }

    /**
     * @return ConfigurableInterface
     */
    public function testSetConfigWithArray()
    {
        $this->obj->setConfig($this->data);
        $this->assertInstanceOf(GenericConfig::class, $this->obj->config());

        $cfg = $this->obj->config();
        $this->assertArraySubsets($this->data, $cfg->data());

        return $this->obj;
    }

    public function testSetConfigWithConfigInstance(): void
    {
        $this->obj->setConfig($this->cfg);

        $cfg = $this->obj->config();
        $this->assertSame($this->cfg, $cfg);
        $this->assertArraySubsets($this->data, $cfg->data());
    }

    public function testSetConfigWithInvalidData(): void
    {
        $this->expectExceptionMessage('Configset must be an associative array, a file path, or an instance of Charcoal\Config\ConfigInterface');
        $this->expectException(InvalidArgumentException::class);
        // phpcs:disable Squiz.Objects.ObjectInstantiation.NotAssigned
        $this->obj->setConfig(new \StdClass);
        // phpcs:enable Squiz.Objects.ObjectInstantiation.NotAssigned
    }



    // Test GetConfig
    // =========================================================================
    /**
     * Asserts that the object will create a new Config
     * if one has not been assigned to object.
     */
    public function testGetConfigCreatesConfig(): void
    {
        $cfg = $this->obj->config();
        $this->assertInstanceOf(GenericConfig::class, $cfg);
    }

    /**
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetConfigWithArray')]
    public function testGetConfigReturnsConfigOnNullKey(ConfigurableInterface $obj): void
    {
        $cfg = $obj->config();
        $this->assertInstanceOf(GenericConfig::class, $cfg);
    }

    /**
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetConfigWithArray')]
    public function testGetConfigReturnsValueOnKey(ConfigurableInterface $obj): void
    {
        $this->assertEquals($this->data['name'], $obj->config('name'));
    }

    /**
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetConfigWithArray')]
    public function testGetConfigReturnsNullOnNonexistentKey(ConfigurableInterface $obj): void
    {
        $this->assertNull($obj->config('charset'));
    }

    /**
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetConfigWithArray')]
    public function testGetConfigReturnsDefaultValueOnNonexistentKey(ConfigurableInterface $obj): void
    {
        $val = $obj->config('charset', 'utf8mb4');
        $this->assertEquals('utf8mb4', $val);
    }

    /**
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetConfigWithArray')]
    public function testGetConfigReturnsFallbackClosureOnNonexistentKey(ConfigurableInterface $obj): void
    {
        $val = $obj->config('charset', fn(): string => 'utf8mb4');
        $this->assertEquals('utf8mb4', $val);
    }

    /**
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetConfigWithArray')]
    public function testGetConfigReturnsFallbackMethodOnNonexistentKey(ConfigurableInterface $obj): void
    {
        $val = $obj->config('charset', [ $this, 'getName' ]);
        $this->assertEquals('testGetConfigReturnsFallbackMethodOnNonexistentKey', $val);
    }

    /**
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     */
    #[\PHPUnit\Framework\Attributes\Depends('testSetConfigWithArray')]
    public function testGetConfigReturnsFallbackFunctionOnNonexistentKey(ConfigurableInterface $obj): void
    {
        $val = $obj->config('charset', 'getcwd');
        $this->assertEquals('getcwd', $val);
    }
}
