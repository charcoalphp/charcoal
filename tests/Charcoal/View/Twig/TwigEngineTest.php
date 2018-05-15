<?php

namespace Charcoal\Tests\View\Twig;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-view'
use Charcoal\View\Twig\TwigEngine;
use Charcoal\View\Twig\TwigLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class TwigEngineTest extends AbstractTestCase
{
    /**
     * @var MustacheEngine
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $logger = new NullLogger();
        $loader = new TwigLoader([
            'logger'    => $logger,
            'base_path' => __DIR__,
            'paths'     => ['templates']
        ]);
        $this->obj = new TwigEngine([
            'logger' => $logger,
            'loader' => $loader
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('twig', $this->obj->type());
    }

    /**
     * @return void
     */
    public function testRender()
    {
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', ['foo'=>'Charcoal'])));
    }

    /**
     * @return void
     */
    public function testRenderTemplate()
    {
        $template = 'Hello {{ foo }}';
        $context = ['foo'=>'World!'];
        $this->assertEquals('Hello World!', trim($this->obj->renderTemplate($template, $context)));
    }
}
