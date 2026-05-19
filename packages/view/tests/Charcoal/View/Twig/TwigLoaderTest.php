<?php

namespace Charcoal\Tests\View\Twig;

use DateTime;

// From Twig
use Twig\Source as TwigSource;

// From 'charcoal-view'
use Charcoal\View\Twig\TwigLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class TwigLoaderTest extends AbstractTestCase
{
    private \Charcoal\View\Twig\TwigLoader $obj;

    public function setUp(): void
    {
        $this->obj = new TwigLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
    }

    public function testLoad(): void
    {
        $ret = $this->obj->load('foo');

        $expected = file_get_contents(__DIR__.'/templates/foo.twig');
        $this->assertEquals($expected, $ret);
    }

    public function testGetSourceContext(): void
    {
        $name = 'foo';
        $ret = $this->obj->getSourceContext($name);

        $source = file_get_contents(__DIR__.'/templates/'.$name.'.twig');
        $expected = new TwigSource($source, $name);
        $this->assertEquals($expected, $ret);
    }

    public function testLoadDynamic(): void
    {
        $this->obj->setDynamicTemplate('widget_template', 'foo');
        $ret = $this->obj->load('$widget_template');

        $expected = file_get_contents(__DIR__.'/templates/foo.twig');
        $this->assertEquals($expected, $ret);
    }

    public function testLoadNotExisting(): void
    {
        $ret = $this->obj->load('foo/bar/foobar');
        $this->assertEquals('foo/bar/foobar', $ret);
    }

    public function testExists(): void
    {
        $this->assertTrue($this->obj->exists('foo'));
        $this->assertFalse($this->obj->exists('foobaz'));
    }

    public function testIsFresh(): void
    {
        $date = new DateTime('2000-01-01');
        $time = $date->getTimestamp();
        $this->assertFalse($this->obj->isFresh('foo', $time));

        $date = new DateTime('2100-01-01');
        $time = $date->getTimestamp();
        $this->assertTrue($this->obj->isFresh('foo', $time));
    }
}
