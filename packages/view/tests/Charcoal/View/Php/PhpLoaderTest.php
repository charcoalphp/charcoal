<?php

declare(strict_types=1);

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
    private \Charcoal\View\Php\PhpLoader $obj;

    public function setUp(): void
    {
        $this->obj = new PhpLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
    }

    public function testLoad(): void
    {
        $ret = $this->obj->load('foo');

        $expected = file_get_contents(__DIR__.'/templates/foo.php');
        $this->assertEquals($expected, $ret);
    }

    public function testLoadDynamic(): void
    {
        $this->obj->setDynamicTemplate('widget_template', 'foo');
        $ret = $this->obj->load('$widget_template');

        $expected = file_get_contents(__DIR__.'/templates/foo.php');
        $this->assertEquals($expected, $ret);
    }

    public function testLoadNotExisting(): void
    {
        $ret = $this->obj->load('foo/bar/foobar');
        $this->assertEquals('foo/bar/foobar', $ret);
    }
}
