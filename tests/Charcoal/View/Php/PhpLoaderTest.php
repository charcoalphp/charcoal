<?php

namespace Charcoal\Tests\View\Php;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\Php\PhpLoader;

/**
 *
 */
class PhpLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MustacheLoader
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new PhpLoader([
            'logger'    => new NullLogger(),
            'base_path' => __DIR__,
            'paths'     => ['templates']
        ]);
    }

    public function testLoad()
    {
        $ret = $this->obj->load('foo');

        $expected = file_get_contents(__DIR__.'/templates/foo.php');
        $this->assertEquals($expected, $ret);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->load(false);
    }

    public function testLoadDynamic()
    {

        $GLOBALS['widget_template'] = 'foo';
        $ret = $this->obj->load('$widget_template');

        $expected = file_get_contents(__DIR__.'/templates/foo.php');
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
}
