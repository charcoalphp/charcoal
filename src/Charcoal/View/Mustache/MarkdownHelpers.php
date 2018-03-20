<?php

namespace Charcoal\View\Mustache;

// From 'mustache/mustache'
use Mustache_LambdaHelper as LambdaHelper;

// From 'parsedown'
use Parsedown;

/**
 *
 */
class MarkdownHelpers implements HelpersInterface
{
    /**
     * Markdown parser object
     * @var Parsedown
     */
    private $parsedown;

    /**
     * MarkdownHelpers constructor.
     * @param array $data Class constructor options / dependencies.
     */
    public function __construct(array $data)
    {
        $this->setParsedown($data['parsedown']);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'markdown' => $this
        ];
    }

    /**
     * @param  string            $text   The markdown text (string) to parse.
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
     * @param Parsedown $parser Thar markdown parser.
     * @return void
     */
    private function setParsedown(Parsedown $parser)
    {
        $this->parsedown = $parser;
    }
}
