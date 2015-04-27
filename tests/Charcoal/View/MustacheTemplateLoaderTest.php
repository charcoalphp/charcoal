<?php

namespace Charcoal\Tests\View;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\View\MustacheTemplateLoader as MustacheTemplateLoader;

class MustacheTemplateLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Charcoal::config()->set_template_path([]);
    }

    public function testConstructor()
    {
        $obj = new MustacheTemplateLoader();
        $this->assertInstanceOf('\Charcoal\View\MustacheTemplateLoader', $obj);
    }

    public function testSearchPathUsesGlobalConfig()
    {
        Charcoal::config()->add_template_path(__DIR__.'/templates');
        $obj = new MustacheTemplateLoader();
        $ret = $obj->search_path();
        $this->assertEquals([__DIR__.'/templates'], $ret);
    }

    public function testLoad()
    {
        Charcoal::config()->add_template_path(__DIR__.'/templates');
        $obj = new MustacheTemplateLoader();
        $ret = $obj->load('foo');

        $expected = file_get_contents(__DIR__.'/templates/foo.php');
        $this->assertEquals($expected, $ret);
    }
}
