<?php

namespace Charcoal\Tests\View;


class ViewableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    static public function setUpBeforeClass()
    {
        include 'ViewableClass.php';
    }

    public function setUp()
    {
        $this->obj = new ViewableClass();
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Tests\View\ViewableClass', $obj);
    }

    public function testSetView()
    {
        include_once 'AbstractViewClass.php';
        $obj = $this->obj;
        $view = new AbstractViewClass();
        $ret = $obj->set_view($view);
        $this->assertSame($ret, $obj);
        $this->assertEquals($view, $obj->view());
    }

    public function testRenderAndDisplay()
    {
        $obj = $this->obj;
        $ret = $obj->render('Hello {{foo}}');
        $this->assertEquals('Hello bar', $ret);

        ob_start();
        $obj->display('Hello {{foo}}');
        $ret2 = ob_get_clean();

        $this->assertEquals($ret, $ret2);

    }
}
