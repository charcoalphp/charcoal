<?php

namespace Charcoal\Tests\View\Php;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-view'
use Charcoal\View\Php\PhpLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class PhpLoaderTest extends AbstractTestCase
{
    /**
     * @var MustacheLoader
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->obj = new PhpLoader([
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

        $expected = file_get_contents(__DIR__.'/templates/foo.php');
        $this->assertEquals($expected, $ret);

        $this->expectException('\InvalidArgumentException');
        $this->obj->load(false);
    }

    /**
     * @return void
     */
    public function testLoadDynamic()
    {
        $GLOBALS['widget_template'] = 'foo';
        $ret = $this->obj->load('$widget_template');

        $expected = file_get_contents(__DIR__.'/templates/foo.php');
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
}
