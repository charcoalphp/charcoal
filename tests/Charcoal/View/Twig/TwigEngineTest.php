<?php

namespace Charcoal\Tests\View\Twig;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\Twig\TwigEngine;
use Charcoal\View\Twig\TwigLoader;

/**
 *
 */
class TwigEngineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MustacheEngine
     */
    private $obj;

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

    public function testType()
    {
        $this->assertEquals('twig', $this->obj->type());
    }

    public function testRender()
    {
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', ['bar'=>'Charcoal'])));
    }

    public function testRenderTemplate()
    {
        $template = 'Hello {{ bar }}';
        $context = ['bar'=>'World!'];
        $this->assertEquals('Hello World!', trim($this->obj->renderTemplate($template, $context)));
    }
}
