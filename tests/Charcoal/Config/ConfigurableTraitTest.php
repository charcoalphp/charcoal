<?php

namespace Charcoal\Tests\Config;

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
             ->method('create_config')
             ->with($this->isType('array'))
             ->will($this->returnCallback(function($args) {
                return new \Charcoal\Config\GenericConfig($args);
             }));

        $this->config = new \Charcoal\Config\GenericConfig();
    }

    /**
     * Assert that the `set_config` method:
     * - is chainable
     * - sets the config properly when passing a ConfigInterface object
     * - sets the config properly when passing an array
     * - throws an exception when passing a non-array or non-object argument
     */
    public function testSetConfig()
    {
        $obj = $this->obj;
        $config = $this->config;

        $ret = $obj->set_config($config);
        $this->assertSame($ret, $obj);
        $this->assertEquals($config, $obj->config());

        $obj->set_config(['foo' => 'baz']);
        $this->assertEquals('baz', $obj->config()->get('foo'));

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_config(false);
    }

    public function testConfigWithKey()
    {
        $obj = $this->obj;
        $obj->set_config(['foo' => 'baz']);
        $this->assertEquals('baz', $obj->config('foo'));

    }
}
