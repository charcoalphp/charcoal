<?php

namespace Charcoal\Tests\View\Twig;

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
     * @var TwigEngine
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $loader = new TwigLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
        $this->obj = new TwigEngine([
                'config'    => false,
                'loader'    => $loader,
                'helpers'   => [],
                'cache'     => false,
                'debug'     => true,
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
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', [ 'foo' => 'Charcoal' ])));
    }

    /**
     * @return void
     */
    public function testRenderTemplate()
    {
        $template = 'Hello {{ foo }}';
        $context  = [ 'foo' => 'World!' ];
        $this->assertEquals('Hello World!', trim($this->obj->renderTemplate($template, $context)));
    }
}
