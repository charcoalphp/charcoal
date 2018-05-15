<?php

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
     *
     * @var Parsedown
     */
    private $parsedown;

    /**
     * @param array $data Class Dependencies.
     */
    public function __construct(array $data)
    {
        $this->setParsedown($data['parsedown']);
    }

    /**
     * Retrieve the helpers.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'markdown' => $this
        ];
    }

    /**
     * Magic: Render the Mustache section.
     *
     * @param  string            $text   The Markdown text to parse.
     * @param  LambdaHelper|null $helper For rendering strings in the current context.
     * @return string
     */
    public function __invoke($text, LambdaHelper $helper = null)
    {
        if ($helper !== null) {
            $text = $helper->render($text);
        }
        return $this->parsedown->text($text);
    }

    /**
     * Set the Markdown parser.
     *
     * @param  Parsedown $parser Thar Markdown parser.
     * @return void
     */
    private function setParsedown(Parsedown $parser)
    {
        $this->parsedown = $parser;
    }
}
