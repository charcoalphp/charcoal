<?php

namespace Charcoal\Tests\Config\Mixin;

// From 'charcoal-config'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\FixturesTrait;
use Charcoal\Tests\Config\Mock\ConfigurableObject;
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;
use Charcoal\Config\ConfigInterface;
use Charcoal\Config\GenericConfig;

/**
 * Test ConfigurableTrait
 *
 * @coversDefaultClass \Charcoal\Config\ConfigurableTrait
 */
class ConfigurableTest extends AbstractTestCase
{
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
     *
     * @return void
     */
    public function setUp()
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
     *
     * @return ConfigurableObject
     */
    public function createObject()
    {
        return new ConfigurableObject();
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

    /**
     * Asserts that the object implements ConfigurableInterface.
     *
     * @coversNothing
     * @return void
     */
    public function testConfigurableInterface()
    {
        $this->assertInstanceOf(ConfigurableInterface::class, $this->obj);
        $this->assertInstanceOf(ConfigInterface::class, $this->obj->createConfig());
    }

    /**
     * Asserts that the config is empty by default.
     *
     * @coversNothing
     * @return void
     */
    public function testDefaultConfigAttribute()
    {
        $this->assertAttributeEmpty('config', $this->obj);
    }



    // Test SetConfig
    // =========================================================================

    /**
     * @covers ::createConfig()
     * @covers ::setConfig()
     * @return void
     */
    public function testSetConfigWithString()
    {
        $path = $this->getPathToFixture('pass/valid.json');
        $that = $this->obj->setConfig($path);
        $this->assertSame($this->obj, $that);
        $this->assertAttributeInstanceOf(GenericConfig::class, 'config', $this->obj);

        $cfg = $this->obj->config();
        $this->assertJsonStringEqualsJsonFile($path, json_encode($cfg));
    }

    /**
     * @covers ::createConfig()
     * @covers ::setConfig()
     * @return ConfigurableInterface
     */
    public function testSetConfigWithArray()
    {
        $this->obj->setConfig($this->data);
        $this->assertAttributeInstanceOf(GenericConfig::class, 'config', $this->obj);

        $cfg = $this->obj->config();
        $this->assertArraySubsets($this->data, $cfg->data());

        return $this->obj;
    }

    /**
     * @covers ::createConfig()
     * @covers ::setConfig()
     * @return void
     */
    public function testSetConfigWithConfigInstance()
    {
        $this->obj->setConfig($this->cfg);
        $this->assertAttributeSame($this->cfg, 'config', $this->obj);

        $cfg = $this->obj->config();
        $this->assertSame($this->cfg, $cfg);
        $this->assertArraySubsets($this->data, $cfg->data());
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Configset must be an associative array, a file path,
     *     or an instance of Charcoal\Config\ConfigInterface
     *
     * @covers ::setConfig()
     * @return void
     */
    public function testSetConfigWithInvalidData()
    {
        // phpcs:disable Squiz.Objects.ObjectInstantiation.NotAssigned
        $this->obj->setConfig(new \StdClass);
        // phpcs:enable Squiz.Objects.ObjectInstantiation.NotAssigned
    }



    // Test GetConfig
    // =========================================================================

    /**
     * Asserts that the object will create a new Config
     * if one has not been assigned to object.
     *
     * @covers ::createConfig()
     * @covers ::config()
     * @return void
     */
    public function testGetConfigCreatesConfig()
    {
        $this->assertAttributeEmpty('config', $this->obj);
        $cfg = $this->obj->config();
        $this->assertInstanceOf(GenericConfig::class, $cfg);
    }

    /**
     * @covers  ::config()
     * @depends testSetConfigWithArray
     *
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     * @return void
     */
    public function testGetConfigReturnsConfigOnNullKey(ConfigurableInterface $obj)
    {
        $cfg = $obj->config(null);
        $this->assertInstanceOf(GenericConfig::class, $cfg);
    }

    /**
     * @covers  ::config()
     * @depends testSetConfigWithArray
     *
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     * @return void
     */
    public function testGetConfigReturnsValueOnKey(ConfigurableInterface $obj)
    {
        $this->assertEquals($this->data['name'], $obj->config('name'));
    }

    /**
     * @covers  ::config()
     * @depends testSetConfigWithArray
     *
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     * @return void
     */
    public function testGetConfigReturnsNullOnNonexistentKey(ConfigurableInterface $obj)
    {
        $this->assertNull($obj->config('charset'));
    }

    /**
     * @covers  ::config()
     * @depends testSetConfigWithArray
     *
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     * @return void
     */
    public function testGetConfigReturnsDefaultValueOnNonexistentKey(ConfigurableInterface $obj)
    {
        $val = $obj->config('charset', 'utf8mb4');
        $this->assertEquals('utf8mb4', $val);
    }

    /**
     * @covers  ::config()
     * @depends testSetConfigWithArray
     *
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     * @return void
     */
    public function testGetConfigReturnsFallbackClosureOnNonexistentKey(ConfigurableInterface $obj)
    {
        $val = $obj->config('charset', function () {
            return 'utf8mb4';
        });
        $this->assertEquals('utf8mb4', $val);
    }

    /**
     * @covers  ::config()
     * @depends testSetConfigWithArray
     *
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     * @return void
     */
    public function testGetConfigReturnsFallbackMethodOnNonexistentKey(ConfigurableInterface $obj)
    {
        $val = $obj->config('charset', [ $this, 'getName' ]);
        $this->assertEquals('testGetConfigReturnsFallbackMethodOnNonexistentKey', $val);
    }

    /**
     * @covers  ::config()
     * @depends testSetConfigWithArray
     *
     * @param  ConfigurableInterface $obj The ConfigurableInterface implementation to test.
     * @return void
     */
    public function testGetConfigReturnsFallbackFunctionOnNonexistentKey(ConfigurableInterface $obj)
    {
        $val = $obj->config('charset', 'getcwd');
        $this->assertEquals('getcwd', $val);
    }
}
