<?php

namespace Charcoal\Tests\Config;

use Charcoal\Config\GenericConfig;

class ConfigurableTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableClass $obj
     */
    public $obj;

    public $config;

    /**
     * Create the Trait stub
     */
    public function setUp()
    {
        $this->obj = $this->getMockForTrait('\Charcoal\Config\ConfigurableTrait');

        $this->obj->expects($this->any())
             ->method('createConfig')
             ->with($this->isType('array'))
             ->will($this->returnCallback(function($args) {
                return new GenericConfig($args);
             }));

        $this->config = new GenericConfig();
    }

    public function testCallingConfigCreatesConfig()
    {
        $config = $this->obj->config();
        $this->assertInstanceOf(GenericConfig::class, $config);
    }

    /**
     * Assert that the `setConfig` method:
     * - is chainable
     * - sets the config properly when passing a ConfigInterface object
     * - sets the config properly when passing an array
     * - throws an exception when passing a non-array or non-object argument
     */
    public function testSetConfig()
    {
        $obj = $this->obj;
        $config = $this->config;

        $ret = $obj->setConfig($config);
        $this->assertSame($ret, $obj);
        $this->assertEquals($config, $obj->config());

        $obj->setConfig(['foo' => 'baz']);
        $this->assertEquals('baz', $obj->config()->get('foo'));

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setConfig(false);
    }

    public function testConfigWithKey()
    {
        $this->assertNull($this->obj->config('foo'));

        $this->obj->setConfig(['foo' => 'baz']);
        $this->assertEquals('baz', $this->obj->config('foo'));
    }
}
