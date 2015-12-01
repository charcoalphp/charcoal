<?php

namespace Charcoal\Tests\View\Mustache;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\View\Mustache\MustacheLoader;

class MustacheLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MustacheLoader
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new MustacheLoader();
        $this->obj->add_search_path(__DIR__.'/templates');
    }

    public function testLoad()
    {
        $ret = $this->obj->load('foo');

        $expected = file_get_contents(__DIR__.'/templates/foo.mustache');
        $this->assertEquals($expected, $ret);
    }
}
