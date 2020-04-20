<?php

namespace Charcoal\Tests\View;

// From Slim
use Slim\Http\Response;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\GenericView;
use Charcoal\View\Renderer;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class RendererTest extends AbstractTestCase
{
    /**
     * Instance of object under test
     * @var AbstractViewClass $obj
     */
    public $obj;

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
            'loader' => $loader,
        ]);
        $view = new GenericView([
            'engine' => $engine,
        ]);

        $this->obj = new Renderer([
            'view' => $view,
        ]);
    }

    /**
     * @return void
     */
    public function testRender()
    {
        $response = new Response();
        $ret = $this->obj->render($response, 'foo', [ 'foo' => 'Charcoal' ]);
        $this->assertEquals($response, $ret);
        $this->assertEquals('Hello Charcoal', trim((string)$ret->getBody()));
    }
}
