<?php

namespace Charcoal\Tests\View;


class AbstractViewControllerTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    static public function setUpBeforeClass()
    {
        include 'AbstractViewControllerClass.php';
    }

    public function setUp()
    {
        $this->obj = new AbstractViewControllerClass();
    }

    public function testSetContext()
    {
        $obj = $this->obj;
        $ret = $obj->set_context(['foo'=>'bar']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['foo'=>'bar'], $obj->context());
    }

    public function testAutoGetWithArrayContext()
    {
        $obj = $this->obj;
        
        $this->assertEquals(null, $obj->foo);
        $this->assertNotTrue(isset($obj->foo));

        $obj->set_context([
            'foo'=>'bar'
        ]);
        $this->assertEquals('bar', $obj->foo);
        $this->assertEquals('bar', $obj->foo());
        $this->assertTrue(isset($obj->foo));

    }
}
