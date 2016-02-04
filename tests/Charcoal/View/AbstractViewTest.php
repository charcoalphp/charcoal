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

    /**
     *
     */
    public function setUp()
    {
        $this->logger = new \Psr\Log\NullLogger();
        $loader = new \Charcoal\View\Mustache\MustacheLoader([
            'logger'=>$this->logger
        ]);
        $engine = new \Charcoal\View\Mustache\MustacheEngine([
            'logger'=>$this->logger,
            'loader'=>$loader
        ]);
        $this->obj = $this->getMockForAbstractClass('\Charcoal\View\AbstractView');
        $this->obj->setLogger($this->logger);
        $this->obj->setEngine($engine);
    }



    /**
     *
     */
    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\View\AbstractView', $obj);
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

        $obj->setTemplate($tpl);
        $obj->setContext($ctx);
        $this->assertEquals('Hello World!', $obj->renderTemplate());

        ob_start();
        echo $obj;
        $output = ob_get_clean();
        $this->assertEquals('Hello World!', $output);

        $this->assertEquals('Hello', $obj->render('Hello'));
        $this->assertEquals('Hello Foo!', $obj->render('Hello {{bar}}', ['bar' => 'Foo!']));
    }

    /**
     *
     */
    public function testRenderTemplate()
    {
        $loader = new \Charcoal\View\Mustache\MustacheLoader([
            'logger'=>$this->logger
        ]);
        $loader->addPath(__DIR__.'/Mustache/templates');

        $engine = new \Charcoal\View\Mustache\MustacheEngine([
            'logger'=>$this->logger,
            'loader'=>$loader
        ]);

        $this->obj->setEngine($engine);
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', ['foo'=>'Charcoal'])));
    }

    /**
     *
     */
    public function testRenderTemplateHelper()
    {
        $loader = new \Charcoal\View\Mustache\MustacheLoader([
            'logger'=>$this->logger
        ]);
        $loader->addPath(__DIR__.'/Mustache/templates');

        $engine = new \Charcoal\View\Mustache\MustacheEngine([
            'logger'=>$this->logger,
            'loader'=>$loader
        ]);

        $this->obj->setEngine($engine);

        $expected = trim('
<div>
    Charcoal
</div>

<!-- Javascript should be printed below: -->

<script>
    window.alert(\'Charcoal Unit Tests\');
</script>');

        $this->assertEquals($expected, trim($this->obj->renderTemplate('helpers', ['foo'=>'Charcoal'])));
    }
}
