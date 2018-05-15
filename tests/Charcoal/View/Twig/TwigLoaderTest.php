<?php

namespace Charcoal\Tests\View\Twig;

use DateTime;

// From PSR-3
use Psr\Log\NullLogger;

// From Twig
use Twig_Source;

// From 'charcoal-view'
use Charcoal\View\Twig\TwigLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class TwigLoaderTest extends AbstractTestCase
{
    /**
     * @var TwigLoader
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->obj = new TwigLoader([
            'logger'    => new NullLogger(),
            'base_path' => __DIR__,
            'paths'     => ['templates']
        ]);
    }

    /**
     * @return void
     */
    public function testLoad()
    {
        $ret = $this->obj->load('foo');

        $expected = file_get_contents(__DIR__.'/templates/foo.twig');
        $this->assertEquals($expected, $ret);

        $this->expectException('\InvalidArgumentException');
        $this->obj->load(false);
    }

    /**
     * @return void
     */
    public function testGetSource()
    {
        $ret = $this->obj->getSource('foo');

        $expected = file_get_contents(__DIR__.'/templates/foo.twig');
        $this->assertEquals($expected, $ret);
    }

    /**
     * @return void
     */
    public function testGetSourceContext()
    {
        $name = 'foo';
        $ret = $this->obj->getSourceContext($name);

        $source = file_get_contents(__DIR__.'/templates/'.$name.'.twig');
        $expected = new Twig_source($source, $name);
        $this->assertEquals($expected, $ret);
    }

    /**
     * @return void
     */
    public function testLoadDynamic()
    {
        $GLOBALS['widget_template'] = 'foo';
        $ret = $this->obj->load('$widget_template');

        $expected = file_get_contents(__DIR__.'/templates/foo.twig');
        $this->assertEquals($expected, $ret);

        $this->expectException('\InvalidArgumentException');
        $GLOBALS['widget_template'] = 1;
        $ret = $this->obj->load('$widget_template');
    }

    /**
     * @return void
     */
    public function testLoadNotExisting()
    {
        $ret = $this->obj->load('foo/bar/foobar');
        $this->assertEquals('foo/bar/foobar', $ret);
    }

    /**
     * @return void
     */
    /*
    public function testFilenameFromIdent()
    {
        $this->assertEquals('foo.mustache', $this->obj->filenameFromIdent('foo'));
        $this->assertEquals('foo/bar.mustache', $this->obj->filenameFromIdent('foo/bar'));
        $this->assertEquals('Foo.Bar.mustache', $this->obj->filenameFromIdent('Foo\Bar'));
    }
    */

    /**
     * @return void
     */
    public function testExists()
    {
        $this->assertTrue($this->obj->exists('foo'));
        $this->assertFalse($this->obj->exists('foobaz'));
    }

    /**
     * @return void
     */
    public function testIsFresh()
    {
        $date = new DateTime('2000-01-01');
        $time = $date->getTimestamp();
        $this->assertFalse($this->obj->isFresh('foo', $time));

        $date = new DateTime('2100-01-01');
        $time = $date->getTimestamp();
        $this->assertTrue($this->obj->isFresh('foo', $time));
    }
}
