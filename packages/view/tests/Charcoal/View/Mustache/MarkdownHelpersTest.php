<?php

namespace Charcoal\Tests\View\Mustache;

// From Mustache
use Mustache_Engine as MustacheEngine;

// From 'erusev/parsedown'
use Parsedown;

// From 'charcoal-view'
use Charcoal\View\Mustache\MarkdownHelpers;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class MarkdownHelpersTest extends AbstractTestCase
{
    private \Charcoal\View\Mustache\MarkdownHelpers $obj;

    private \Mustache_Engine $mustache;

    public function setUp(): void
    {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);
        $this->obj = new MarkdownHelpers([
            'parsedown' => $parsedown,
        ]);
        $this->mustache = new MustacheEngine([
            'helpers' => $this->obj->toArray(),
        ]);
    }

    public function testMarkdown(): void
    {
        $template = $this->mustache->loadTemplate(
            '{{# markdown }}**test**{{/ markdown }}'
        );

        $ret = $template->render();
        $this->assertStringContainsString('<strong>test</strong>', $ret);
    }
}
