<?php

namespace Charcoal\Tests\View;

use \Charcoal\Model\Model as Model;
use \Charcoal\Model\ModelMetadata as Metadata;

class AbstractViewTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    static public function setUpBeforeClass()
    {
        include 'AbstractViewClass.php';
    }

    public function setUp()
    {
        $this->obj = new AbstractViewClass();
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\View\AbstractView', $obj);
    }

    public function testContructorWithData()
    {
        $obj = new AbstractViewClass([
            'template'=>'foo',
            'context'=>['bar'=>'baz']
        ]);
        $this->assertEquals('foo', $obj->template());
        $this->assertEquals(['bar'=>'baz'], $obj->context());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data([
            'template'=>'foo',
            'context'=>['bar'=>'baz']

        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->template());
        $this->assertEquals(['bar'=>'baz'], $obj->context());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_data(false);
    }

    public function testSetTemplate()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->template());

        $ret = $obj->set_template('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->template());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_template(false);
    }

    public function testSetContext()
    {
        $obj = $this->obj;
        $this->assertEquals(null, $obj->context());
        
        $ret = $obj->set_context(['foo'=>1]);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['foo'=>1], $obj->context());

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->set_context(false);
    }

    public function testRender()
    {
        $obj = new AbstractViewClass();
        $obj->set_template('Hello {{who}}');
        $obj->set_context(['who'=>'World!']);
        $this->assertEquals('Hello World!', $obj->render());

        ob_start();
        echo $obj;
        $output = ob_get_clean();
        $this->assertEquals('Hello World!', $output);

        $obj2 = new AbstractViewClass();
        $this->assertEquals('Hello', $obj->render('Hello'));

        $obj3 = new AbstractViewClass();
        $this->assertEquals('Hello World!', $obj->render('Hello {{who}}', ['who'=>'World!']));

    }
}
