<?php

namespace Charcoal\Tests\View;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use \Slim\Http\Response;

use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\GenericView;
use Charcoal\View\Renderer;

class RendererTest extends PHPUnit_Framework_TestCase
{
    /**
     * Instance of object under test
     * @var AbstractViewClass $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $logger = new NullLogger();
        $loader = new MustacheLoader([
            'logger'=>$logger,
            'base_path'=>__DIR__,
            'paths'=>['Mustache/templates']
        ]);
        $engine = new MustacheEngine([
            'logger'=>$logger,
            'loader'=>$loader
        ]);
        $view = new GenericView([
            'logger' => $logger,
            'engine' => $engine
        ]);

        $this->obj = new Renderer([
            'view'  => $view
        ]);
    }

    public function testRender()
    {
        $response = new Response();
        $ret = $this->obj->render($response, 'foo', ['foo'=>'Charcoal']);
        $this->assertEquals($response, $ret);
        $this->assertEquals('Hello Charcoal', trim((string)$ret->getBody()));
    }
}
