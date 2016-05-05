<?php

namespace Charcoal\Tests\View\Mustache;


use \Charcoal\View\Mustache\MustacheLoader;

/**
 *
 */
class MustacheLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MustacheLoader
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new MustacheLoader([
            'logger'=>new \Psr\Log\NullLogger()
        ]);
    }

    public function testDefaults()
    {
        $this->assertEquals('', $this->obj->basePath());
        $this->assertEquals([], $this->obj->paths());
    }

    public function testLoad()
    {
        // Set the path to the unit tests example templates
        $this->obj->addPath(__DIR__.'/templates');

        $ret = $this->obj->load('foo');

        $expected = file_get_contents(__DIR__.'/templates/foo.mustache');
        $this->assertEquals($expected, $ret);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->load(false);
    }

    public function testLoadDynamic()
    {
        // Set the path to the unit tests example templates
        $this->obj->addPath(__DIR__.'/templates');

        $GLOBALS['widget_template'] = 'foo';
        $ret = $this->obj->load('$widget_template');

        $expected = file_get_contents(__DIR__.'/templates/foo.mustache');
        $this->assertEquals($expected, $ret);

        $this->setExpectedException('\InvalidArgumentException');
        $GLOBALS['widget_template'] = false;
        $ret = $this->obj->load('$widget_template');
    }

    public function testLoadNotExisting()
    {
        // Set the path to the unit tests example templates
        $this->obj->addPath(__DIR__.'/templates');

        $ret = $this->obj->load('foo/bar/foobar');
        $this->assertEquals('foo/bar/foobar', $ret);
    }

    public function testFilenameFromIdent()
    {
        $this->assertEquals('foo.mustache', $this->obj->filenameFromIdent('foo'));
        $this->assertEquals('foo/bar.mustache', $this->obj->filenameFromIdent('foo/bar'));
        $this->assertEquals('Foo.Bar.mustache', $this->obj->filenameFromIdent('Foo\Bar'));
    }

    /**
     * @dataProvider providerClassnameIdent
     */
    public function testClassnameToIdent($class, $ident)
    {
        $this->assertEquals($ident, $this->obj->classnameToIdent($class));
    }

    public function providerClassnameIdent()
    {
        return [
            ['\Foo\Bar\Baz', 'foo/bar/baz'],
            ['Foobar', 'foobar']
        ];
    }

}
