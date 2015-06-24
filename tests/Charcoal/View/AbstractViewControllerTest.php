<?php

namespace Charcoal\Tests\View;


class AbstractViewControllerTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    static public function setUpBeforeClass()
    {
        include_once 'AbstractViewControllerClass.php';
        include_once 'ContextClass.php';
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

        $obj->set_context(
            [
            'foo'=>'bar'
            ]
        );
        $this->assertEquals('bar', $obj->foo);
        $this->assertEquals('bar', $obj->foo());
        $this->assertTrue(isset($obj->foo));
    }

    public function testAutoGetWithObjectContext()
    {
        $obj = $this->obj;

        $this->assertEquals(null, $obj->foo);
        $this->assertNotTrue(isset($obj->foo));

        $this->assertEquals(null, $obj->baz);
        $this->assertNotTrue(isset($obj->baz));
        
        $ctx = new ContextClass();
        $ctx->set_foo('bar');
        $ctx->baz = 'test';
        $obj->set_context($ctx);

        $this->assertEquals('bar', $obj->foo);
        $this->assertEquals('bar', $obj->foo());
        $this->assertTrue(isset($obj->foo));

        $this->assertEquals('test', $obj->baz);
        $this->assertEquals('test', $obj->baz());
        $this->assertTrue(isset($obj->baz));
    }
}
