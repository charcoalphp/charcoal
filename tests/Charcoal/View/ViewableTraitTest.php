<?php

namespace Charcoal\Tests\View;

use \Charcoal\View\AbstractView as AbstractView;

class ViewableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    static public function setUpBeforeClass()
    {
        include_once 'ViewableClass.php';
        include_once 'AbstractViewClass.php';
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

    public function testSetTemplateEngine()
    {
        $obj = $this->obj;
        $this->assertEquals(AbstractView::DEFAULT_ENGINE, $obj->template_engine());
        $ret = $obj->set_template_engine('php');
        $this->assertSame($ret, $obj);
        $this->assertEquals('php', $obj->template_engine());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_template_engine(false);
    }

    public function testSetView()
    {
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
