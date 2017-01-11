<?php

namespace Charcoal\Tests\View;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\ViewableTrait;
use Charcoal\View\AbstractView;
use Charcoal\View\GenericView;
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\ViewableTrait as MockTrait;

/**
 *
 */
class ViewableTraitTest extends PHPUnit_Framework_TestCase
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
     *
     */
    public function setUp()
    {
        $logger = new NullLogger();
        $loader = new MustacheLoader([
            'logger'    => $logger,
            'base_path' => __DIR__,
            'paths'     => ['Mustache/templates']
        ]);
        $engine = new MustacheEngine([
            'logger'    => $logger,
            'loader'    => $loader
        ]);
        $genericView = new GenericView([
            'logger'    => $logger,
            'engine'    => $engine
        ]);

        $mock = $this->getMockForTrait(MockTrait::class);

        $mock->setView($genericView);
        $this->assertSame($genericView, $mock->view());

        $mock->foo = 'bar';
        $this->obj = $mock;
    }

    /**
     *
     */
    public function testSetTemplateIdent()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->templateIdent());

        $ret = $obj->setTemplateIdent('foobar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foobar', $obj->templateIdent());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setTemplateIdent(false);
    }

    public function testRenderWithTemplateIdent()
    {
        $this->obj->foo = 'bar';
        $ret = $this->obj->render('foo');
        $this->assertEquals('Hello bar', trim($ret));
    }

    public function testRender()
    {
        $this->obj->setTemplateIdent('foo');
        $this->obj->foo = 'bar';
        $ret = $this->obj->render();
        $this->assertEquals('Hello bar', trim($ret));
    }

    /**
     *
     */
    public function testRenderTemplate()
    {
        $this->obj->foo = 'bar';
        $ret = $this->obj->renderTemplate('Hello {{foo}}');
        $this->assertEquals('Hello bar', $ret);
    }

    public function testToString()
    {
        $this->obj->setTemplateIdent('foo');
        $this->obj->foo = 'bar';
        $this->assertEquals('Hello bar', trim((string)$this->obj));
    }

    public function testSetViewController()
    {
        $this->assertSame($this->obj, $this->obj->viewController());

        $ret = $this->obj->setViewController([]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([], $this->obj->viewController());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setViewController('foo');
    }
}
