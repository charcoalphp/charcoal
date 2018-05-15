<?php

namespace Charcoal\Tests\View;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Mustache\AssetsHelpers;
use Charcoal\View\Mustache\TranslatorHelpers;
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
     *
     * @return void
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
     * @return void
     */
    public function testRenderTemplate()
    {
        $this->assertEquals('Hello', $this->obj->renderTemplate('Hello'));
        $this->assertEquals('Hello Foo!', $this->obj->renderTemplate('Hello {{bar}}', ['bar' => 'Foo!']));
        $this->assertEquals('Hello ', $this->obj->renderTemplate('Hello {{bar}}', ['baz' => 'Foo!']));
    }

    /**
     * @return void
     */
    public function testRender()
    {
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', [ 'foo' => 'Charcoal' ])));
    }

    /**
     * @return void
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

    /**
     * @return void
     */
    public function testLoadTemplateInvalidStringThrowsException()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->loadTemplate(false);
    }

    /**
     * @return void
     */
    public function testLoadTemplateEmptyStringReturnsEmpty()
    {
        $this->assertEquals('', $this->obj->loadTemplate(''));
    }

    /**
     * @return void
     */
    public function testSetDynamicTemplate()
    {
        $this->obj->setDynamicTemplate('dynamic', 'foo');
        $ret = $this->obj->renderTemplate('{{> $dynamic }}', ['foo'=>'Dynamic']);
        $this->assertEquals('Hello Dynamic', trim($ret));
    }
}
