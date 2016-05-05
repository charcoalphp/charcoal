<?php

namespace Charcoal\Tests\View\Mustache;

use \Charcoal\View\Mustache\GenericHelper;

class GenericHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MustacheEngine
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new GenericHelper;
    }

    public function testDefaults()
    {
        $this->assertEquals('', $this->obj->js());
        $this->assertEquals('', $this->obj->css());
        $this->assertEquals('', $this->obj->jsRequirements());
        $this->assertEquals('', $this->obj->cssRequirements());
    }

    public function testAddJs()
    {
        $this->obj->addJs('foo');
        $this->assertEquals('foo', $this->obj->js());
    }

    public function testAddCss()
    {
        $this->obj->addCss('foo');
        $this->assertEquals('foo', $this->obj->css());
    }

    public function testAddJsRequirement()
    {
        $this->obj->addJsRequirement('foo');
        $this->obj->addJsRequirement('bar');
        $this->assertEquals('foo'."\n".'bar', $this->obj->jsRequirements());
        $this->assertEquals('', $this->obj->jsRequirements());
    }

    public function testAddCssRequirement()
    {
        $this->obj->addCssRequirement('foo');
        $this->obj->addCssRequirement('bar');
        $this->assertEquals('foo'."\n".'bar', $this->obj->cssRequirements());
        $this->assertEquals('', $this->obj->cssRequirements());
    }
}
