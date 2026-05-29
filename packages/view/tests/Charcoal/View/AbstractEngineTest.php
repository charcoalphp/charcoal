<?php

namespace Charcoal\Tests\View;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\AbstractEngine;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractEngineTest extends AbstractTestCase
{
    /**
     * Instance of object under test
     * @var AbstractEngine $obj
     */
    public $obj;

    public function setUp(): void
    {
        $loader = new MustacheLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'Mustache/templates' ],
        ]);
        $this->obj = new class ([
            'loader' => $loader,
        ]) extends AbstractEngine {
            public function type(): string { }
            public function renderTemplate(string $templateString, $context): string { }
        };
    }

    public function testLoadTemplate(): void
    {
        $this->assertEquals('', $this->obj->loadTemplate(''));

        $expected = file_get_contents(__DIR__.'/Mustache/templates/foo.mustache');
        $this->assertEquals($expected, $this->obj->loadTemplate('foo'));
    }

    public function testSetDynamicTemplate(): void
    {
        $this->assertNull($this->obj->setDynamicTemplate('foo', 'bar'));
    }
}
