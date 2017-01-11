<?php

namespace Charcoal\Tests\View;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\ViewableTrait;
use Charcoal\View\AbstractView;
use Charcoal\View\GenericView;
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;

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
            'logger'=>$logger
        ]);
        $engine = new MustacheEngine([
            'logger'=>$logger,
            'loader'=>$loader
        ]);
        $genericView = new GenericView([
            'logger'=>$logger,
            'engine'=>$engine
        ]);

        $mock = $this->getMockForTrait('\Charcoal\View\ViewableTrait');
        $mock->setView($genericView);
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

    /**
     *
     */
    // public function testSetView()
    // {
    //     $obj = $this->obj;

    //     $view = new GenericView([
    //         'logger'=>new \Psr\Log\NullLogger()
    //     ]);

    //     $ret = $obj->setView($view);
    //     $this->assertSame($ret, $obj);
    //     $this->assertEquals($view, $obj->view());
    // }

    /**
     *
     */
    public function testRenderAndDisplay()
    {
        $obj = $this->obj;
        $obj->foo = 'bar';
        $ret = $obj->renderTemplate('Hello {{foo}}');
        $this->assertEquals('Hello bar', $ret);
    }
}
