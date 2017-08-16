<?php

namespace Charcoal\Tests\View\Mustache;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\Mustache\MustacheLoader;

/**
 *
 */
class MustacheLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MustacheLoader
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new MustacheLoader([
            'logger'    => new NullLogger(),
            'base_path' => __DIR__,
            'paths'     => ['templates']
        ]);
    }

    /**
     * @dataProvider templateProvider
     */
    public function testLoad($template)
    {
        $ret = $this->obj->load($template);

        $expected = file_get_contents(__DIR__.'/templates/'.$template.'.mustache');
        $this->assertEquals($expected, $ret);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->load(false);
    }

    /**
     * @dataProvider templateProvider
     */
    public function testLoadDynamicLegacy($template)
    {
        $GLOBALS['widget_template'] = $template;
        $ret = $this->obj->load('$widget_template');

        $expected = file_get_contents(__DIR__.'/templates/'.$template.'.mustache');
        $this->assertEquals($expected, $ret);
    }

    public function testLoadDynamicLegacyInvalidException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $GLOBALS['widget_template'] = 1;
        $ret = $this->obj->load('$widget_template');
    }

    /**
     * @dataProvider templateProvider
     */
    public function testLoadDynamic($template)
    {
        $this->obj->setDynamicTemplate('dynamic', $template);
        $ret = $this->obj->load('$dynamic');

        $expected = file_get_contents(__DIR__.'/templates/'.$template.'.mustache');
        $this->assertEquals($expected, $ret);
    }

    public function testLoadNotExisting()
    {
        $ret = $this->obj->load('foo/bar/foobar');
        $this->assertEquals('foo/bar/foobar', $ret);
    }

    // public function testFilenameFromIdent()
    // {
    //     $this->assertEquals('foo.mustache', $this->obj->filenameFromIdent('foo'));
    //     $this->assertEquals('foo/bar.mustache', $this->obj->filenameFromIdent('foo/bar'));
    //     $this->assertEquals('Foo.Bar.mustache', $this->obj->filenameFromIdent('Foo\Bar'));
    // }

    public function templateProvider()
    {
        return [
            ['foo'],
            ['helpers']
        ];
    }
}
