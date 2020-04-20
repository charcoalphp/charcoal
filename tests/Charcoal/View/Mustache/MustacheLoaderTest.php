<?php

namespace Charcoal\Tests\View\Mustache;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class MustacheLoaderTest extends AbstractTestCase
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
        $this->obj = new MustacheLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
    }

    /**
     * @dataProvider templateProvider
     *
     * @param  string $template The template to load.
     * @return void
     */
    public function testLoad($template)
    {
        $ret = $this->obj->load($template);

        $expected = file_get_contents(__DIR__.'/templates/'.$template.'.mustache');
        $this->assertEquals($expected, $ret);

        $this->expectException('\InvalidArgumentException');
        $this->obj->load(false);
    }

    /**
     * @dataProvider templateProvider
     *
     * @param  string $template The template to load.
     * @return void
     */
    public function testLoadDynamic($template)
    {
        $this->obj->setDynamicTemplate('dynamic', $template);
        $ret = $this->obj->load('$dynamic');

        $expected = file_get_contents(__DIR__.'/templates/'.$template.'.mustache');
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

    /**
     * @return array
     */
    public function templateProvider()
    {
        return [
            [ 'foo' ],
            [ 'helpers' ],
        ];
    }
}
