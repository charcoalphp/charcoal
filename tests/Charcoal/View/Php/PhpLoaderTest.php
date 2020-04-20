<?php

namespace Charcoal\Tests\View\Php;

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
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
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
        $this->obj->setDynamicTemplate('widget_template', 'foo');
        $ret = $this->obj->load('$widget_template');

        $expected = file_get_contents(__DIR__.'/templates/foo.php');
        $this->assertEquals($expected, $ret);
    }

    /**
     * @return void
     */
    public function testLoadNotExisting()
    {
        $ret = $this->obj->load('foo/bar/foobar');
        $this->assertEquals('foo/bar/foobar', $ret);
    }
}
