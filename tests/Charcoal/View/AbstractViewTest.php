<?php

namespace Charcoal\Tests\View;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\AbstractView;

/**
 *
 */
class AbstractViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * Instance of object under test
     * @var AbstractViewClass $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $logger = new NullLogger();
        $loader = new MustacheLoader([
            'logger'=>$logger,
            'base_path'=>__DIR__,
            'paths'=>['Mustache/templates']
        ]);
        $engine = new MustacheEngine([
            'logger'=>$logger,
            'loader'=>$loader
        ]);
        $this->obj = $this->getMockForAbstractClass(AbstractView::class, [[
            'logger' => $logger,
            'engine' => $engine
        ]]);
    }

    /**
     *
     */
    public function testRenderTemplate()
    {
        $this->assertEquals('Hello', $this->obj->renderTemplate('Hello'));
        $this->assertEquals('Hello Foo!', $this->obj->renderTemplate('Hello {{bar}}', ['bar' => 'Foo!']));
        $this->assertEquals('Hello ', $this->obj->renderTemplate('Hello {{bar}}', ['baz' => 'Foo!']));
    }

    /**
     *
     */
    public function testRender()
    {
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', ['foo'=>'Charcoal'])));
    }

    /**
     *
     */
    public function testRenderTemplateHelper()
    {

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

    public function testLoadTemplateInvalidStringThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->loadTemplate(false);
    }

    public function testLoadTemplateEmptyStringReturnsEmpty()
    {
        $this->assertEquals('', $this->obj->loadTemplate(''));
    }
}
