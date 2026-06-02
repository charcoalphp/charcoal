<?php

declare(strict_types=1);

namespace Charcoal\Tests\View\Mustache;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class MustacheLoaderTest extends AbstractTestCase
{
    private \Charcoal\View\Mustache\MustacheLoader $obj;

    public function setUp(): void
    {
        $this->obj = new MustacheLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
    }

    /**
     *
     * @param  string $template The template to load.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('templateProvider')]
    public function testLoad(string $template): void
    {
        $ret = $this->obj->load($template);

        $expected = file_get_contents(__DIR__.'/templates/'.$template.'.mustache');
        $this->assertEquals($expected, $ret);
    }

    /**
     *
     * @param  string $template The template to load.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('templateProvider')]
    public function testLoadDynamic(string $template): void
    {
        $this->obj->setDynamicTemplate('dynamic', $template);
        $ret = $this->obj->load('$dynamic');

        $expected = file_get_contents(__DIR__.'/templates/'.$template.'.mustache');
        $this->assertEquals($expected, $ret);
    }

    public function testLoadNotExisting(): void
    {
        $ret = $this->obj->load('foo/bar/foobar');
        $this->assertEquals('foo/bar/foobar', $ret);
    }

    public static function templateProvider(): array
    {
        return [
            [ 'foo' ],
            [ 'helpers' ],
        ];
    }
}
