<?php

namespace Charcoal\Tests\View;

use \Charcoal\View\ViewableTrait;
use \Charcoal\View\AbstractView;
use \Charcoal\View\GenericView;

class ViewableTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ViewableTrait $obj
     */
    private $obj;

    /**
     * @var AbtractView $view
     */
    private $view;


    public function setUp()
    {
        $generic_view = new GenericView([
            'logger'=>new \Monolog\Logger('charcoal.test')
        ]);
        $mock = $this->getMockForTrait('\Charcoal\View\ViewableTrait');
        $mock->method('create_view')
             ->willReturn($generic_view);
        $mock->foo = 'bar';
        $this->obj = $mock;

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

    public function testSetTemplateIdent()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->template_ident());

        $ret = $obj->set_template_ident('foobar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foobar', $obj->template_ident());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_template_ident(false);
    }

    public function testSetView()
    {
        $obj = $this->obj;

        $view = $this->obj->create_view();

        $ret = $obj->set_view($view);
        $this->assertSame($ret, $obj);
        $this->assertEquals($view, $obj->view());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_view(false);
    }

    public function testRenderAndDisplay()
    {
        $obj = $this->obj;
        $obj->foo = 'bar';
        $ret = $obj->render('Hello {{foo}}');
        $this->assertEquals('Hello bar', $ret);

        ob_start();
        $obj->render('Hello {{foo}}');
        $ret2 = ob_get_clean();

        $this->assertEquals($ret, $ret2);
    }
}
