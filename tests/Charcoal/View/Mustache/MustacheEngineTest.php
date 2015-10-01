<?php

namespace Charcoal\Tests\View\Mustache;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\View\Mustache\MustacheEngine;

class MustacheEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
    * @var MustacheEngine
    */
    private $obj;

    public function setUp()
    {
        $this->obj = new MustacheEngine([
            'logger'=>null,
            
        ]);
    }
    public function testConstructor()
    {
        $this->assertInstanceOf('\Charcoal\View\Mustache\MustacheEngine', $this->obj);
    }
}
