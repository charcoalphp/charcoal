<?php

namespace Charcoal\Tests\View;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Mustache\AssetsHelpers;
use Charcoal\View\AbstractView;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractViewTest extends AbstractTestCase
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
    public function setUp(): void
    {
        $loader = new MustacheLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'Mustache/templates' ],
        ]);

        $assets = new AssetsHelpers();
        $engine = new MustacheEngine([
            'loader'  => $loader,
            'helpers' => $assets->toArray(),
        ]);
        $this->obj = new class ([
            'engine' => $engine,
        ]) extends AbstractView {};
    }

    public function testRenderTemplate(): void
    {
        $this->assertEquals('Hello', $this->obj->renderTemplate('Hello'));
        $this->assertEquals('Hello Foo!', $this->obj->renderTemplate('Hello {{bar}}', [ 'bar' => 'Foo!' ]));
        $this->assertEquals('Hello ', $this->obj->renderTemplate('Hello {{bar}}', [ 'baz' => 'Foo!' ]));
    }

    public function testRender(): void
    {
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', [ 'foo' => 'Charcoal' ])));
    }

    public function testRenderTemplateHelper(): void
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

    public function testLoadTemplateEmptyStringReturnsEmpty(): void
    {
        $this->assertEquals('', $this->obj->loadTemplate(''));
    }

    public function testLoadTemplateFile(): void
    {
        $this->assertEquals("Hello {{foo}}\n", $this->obj->loadTemplate('foo'));
    }

    public function testSetDynamicTemplate(): void
    {
        $this->obj->setDynamicTemplate('dynamic', 'foo');
        $ret = $this->obj->renderTemplate('{{> $dynamic }}', [ 'foo' => 'Dynamic' ]);
        $this->assertEquals('Hello Dynamic', trim($ret));
    }
}
