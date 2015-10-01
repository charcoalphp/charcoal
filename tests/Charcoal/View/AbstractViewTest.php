<?php

namespace Charcoal\Tests\View;

use \Charcoal\Model\Model as Model;
use \Charcoal\Model\ModelMetadata as Metadata;

class AbstractViewTest extends \PHPUnit_Framework_TestCase
{
    /**
    * @var AbstractViewClass $obj
    */
    public $obj;

    public function setUp()
    {
        $view_args = [
            'logger'=>null
        ];
        $this->obj = $this->getMockForAbstractClass('\Charcoal\View\AbstractView', $view_args);
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\View\AbstractView', $obj);
    }


    public function testSetEngineType()
    {
        $obj = $this->obj;
        $this->assertEquals('mustache', $obj->engine_type());

        $ret = $obj->set_engine_type('php');
        $this->assertSame($ret, $obj);
        $this->assertEquals('php', $obj->engine_type());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_engine_type(1);
    }

    /**
    * Asserts that the render() method:
    * - Can be used without parameters
    * - Can be used only with the template parameter
    * - Can be used with a template and a context parameter
    * - Is called when casting to string
    */
    public function testRender()
    {
        $obj = $this->obj;
        $tpl = 'Hello {{who}}';
        $ctx = ['who' => 'World!'];

        $obj->set_template($tpl);
        $obj->set_context($ctx);
        $this->assertEquals('Hello World!', $obj->render());

        ob_start();
        echo $obj;
        $output = ob_get_clean();
        $this->assertEquals('Hello World!', $output);

        $this->assertEquals('Hello', $obj->render('Hello'));
        $this->assertEquals('Hello Foo!', $obj->render('Hello {{bar}}', ['bar' => 'Foo!']));
    }
}
