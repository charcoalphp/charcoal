<?php

namespace Charcoal\Tests\View;

// From 'charcoal-view'
use Charcoal\View\ViewableTrait;
use Charcoal\View\AbstractView;
use Charcoal\View\GenericView;
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\ViewableTrait as MockTrait;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ViewableTraitTest extends AbstractTestCase
{
    /**
     * @var ViewableTrait $obj
     */
    private $obj;

    /**
     * @var AbtractView $view
     */
    private $view;

    /**
     * @return void
     */
    public function setUp()
    {
        $loader = new MustacheLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'Mustache/templates' ],
        ]);
        $engine = new MustacheEngine([
            'loader'    => $loader,
        ]);
        $genericView = new GenericView([
            'engine'    => $engine,
        ]);

        $mock = $this->getMockForTrait(MockTrait::class);

        $mock->setView($genericView);
        $this->assertSame($genericView, $mock->view());

        $mock->foo = 'bar';
        $this->obj = $mock;
    }

    /**
     * @return void
     */
    public function testSetTemplateIdent()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->templateIdent());

        $ret = $obj->setTemplateIdent('foobar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foobar', $obj->templateIdent());

        $this->expectException('\InvalidArgumentException');
        $obj->setTemplateIdent(false);
    }

    /**
     * @return void
     */
    public function testRenderWithTemplateIdent()
    {
        $this->obj->foo = 'bar';
        $ret = $this->obj->render('foo');
        $this->assertEquals('Hello bar', trim($ret));
    }

    /**
     * @return void
     */
    public function testRender()
    {
        $this->obj->setTemplateIdent('foo');
        $this->obj->foo = 'bar';
        $ret = $this->obj->render();
        $this->assertEquals('Hello bar', trim($ret));
    }

    /**
     * @return void
     */
    public function testRenderTemplate()
    {
        $this->obj->foo = 'bar';
        $ret = $this->obj->renderTemplate('Hello {{foo}}');
        $this->assertEquals('Hello bar', $ret);
    }

    /**
     * @return void
     */
    public function testToString()
    {
        $this->obj->setTemplateIdent('foo');
        $this->obj->foo = 'bar';
        $this->assertEquals('Hello bar', trim((string)$this->obj));
    }

    /**
     * @return void
     */
    public function testSetViewController()
    {
        $this->assertSame($this->obj, $this->obj->viewController());

        $ret = $this->obj->setViewController([]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([], $this->obj->viewController());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setViewController('foo');
    }

    /**
     * @return void
     */
    public function testSetDynamicTemplate()
    {
        $this->assertNull($this->obj->setDynamicTemplate('foo', 'bar'));
    }
}
