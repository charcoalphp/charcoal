<?php

namespace Charcoal\Tests\Config;


class ConfigurableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    static public function setUpBeforeClass()
    {
        include_once 'ConfigurableClass.php';
    }

    public function setUp()
    {
        $this->obj = new ConfigurableClass();
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Tests\Config\ConfigurableClass', $obj);
    }


    public function testSetConfig()
    {
        include_once 'AbstractConfigClass.php';
        $obj = $this->obj;
        $config = new AbstractConfigClass();
        $ret = $obj->set_config($config);
        $this->assertSame($ret, $obj);
        $this->assertEquals($config, $obj->config());

        $config = ['foo'=>'baz'];
        $obj->set_config($config);
        $this->assertEquals('baz', $obj->config()->foo());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_config(false);
    }
}
