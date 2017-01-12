<?php

namespace Charcoal\Tests\View\Twig;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Twig_Source;

use Charcoal\View\Twig\TwigLoader;

/**
 *
 */
class TwigLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var TwigLoader
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new TwigLoader([
            'logger'    => new NullLogger(),
            'base_path' => __DIR__,
            'paths'     => ['templates']
        ]);
    }

    public function testLoad()
    {
        $ret = $this->obj->load('foo');

        $expected = file_get_contents(__DIR__.'/templates/foo.twig');
        $this->assertEquals($expected, $ret);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->load(false);
    }

    public function testGetSource()
    {
        $ret = $this->obj->getSource('foo');

        $expected = file_get_contents(__DIR__.'/templates/foo.twig');
        $this->assertEquals($expected, $ret);
    }

    public function testGetSourceContext()
    {
        $name = 'foo';
        $ret = $this->obj->getSourceContext($name);

        $source = file_get_contents(__DIR__.'/templates/'.$name.'.twig');
        $expected = new Twig_source($source, $name);
        $this->assertEquals($expected, $ret);


    }

    public function testLoadDynamic()
    {

        $GLOBALS['widget_template'] = 'foo';
        $ret = $this->obj->load('$widget_template');

        $expected = file_get_contents(__DIR__.'/templates/foo.twig');
        $this->assertEquals($expected, $ret);

        $this->setExpectedException('\InvalidArgumentException');
        $GLOBALS['widget_template'] = false;
        $ret = $this->obj->load('$widget_template');
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

    public function testExists()
    {
        $this->assertTrue($this->obj->exists('foo'));
        $this->assertFalse($this->obj->exists('foobaz'));
    }
}
