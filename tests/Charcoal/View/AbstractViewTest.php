<?php

namespace Charcoal\Tests\View;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Mustache\AssetsHelpers;
use Charcoal\View\Mustache\TranslatorHelpers;
use Charcoal\View\AbstractView;

/**
 *
 */
class AbstractViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var AbstractView
     */
    public $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $logger = new NullLogger();
        $loader = new MustacheLoader([
            'logger'    => $logger,
            'base_path' => __DIR__,
            'paths'     => [ 'Mustache/templates' ]
        ]);

        $assets = new AssetsHelpers();
        $i18n   = new TranslatorHelpers();
        $engine = new MustacheEngine([
            'logger'  => $logger,
            'loader'  => $loader,
            'helpers' => array_merge($assets->toArray(), $i18n->toArray())
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
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', [ 'foo' => 'Charcoal' ])));
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

        $this->assertEquals($expected, trim($this->obj->renderTemplate('helpers', [ 'foo' => 'Charcoal' ])));
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
