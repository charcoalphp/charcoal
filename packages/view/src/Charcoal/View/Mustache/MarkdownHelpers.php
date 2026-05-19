<?php

declare(strict_types=1);

namespace Charcoal\View\Mustache;

// From Mustache
use Mustache_LambdaHelper as LambdaHelper;
// From 'erusev/parsedown'
use Parsedown;

/**
 * Mustache helpers for rendering Markdown syntax.
 */
class MarkdownHelpers implements HelpersInterface
{
    /**
     * Store the Markdown parser.
     */
    private \Parsedown $parsedown;

    /**
     * @param array $data Class Dependencies.
     */
    public function __construct(array $data)
    {
        $this->setParsedown($data['parsedown']);
    }

    /**
     * Retrieve the helpers.
     */
    public function toArray(): array
    {
        return [
            'markdown' => $this,
        ];
    }

    /**
     * Magic: Render the Mustache section.
     *
     * @param  string            $text   The Markdown text to parse.
     * @param  LambdaHelper|null $helper For rendering strings in the current context.
     */
    public function __invoke($text, ?LambdaHelper $helper = null): string
    {
        if ($helper instanceof \Mustache_LambdaHelper) {
            $text = $helper->render($text);
        }
        return $this->parsedown->text($text);
    }

    /**
     * Set the Markdown parser.
     *
     * @param  Parsedown $parser Thar Markdown parser.
     */
    private function setParsedown(Parsedown $parser): void
    {
        $this->parsedown = $parser;
    }
}
