<?php

declare(strict_types=1);

namespace Charcoal\View\Twig;

// From Twig
use Twig_Environment;

// From 'charcoal-view'
use Charcoal\View\AbstractEngine;

/**
 *
 */
class TwigEngine extends AbstractEngine
{
    const DEFAULT_CACHE_PATH = '../cache/twig';

    /**
     * @var Twig_Environment $twig
     */
    private $twig;

    /**
     * @return string
     */
    public function type(): string
    {
        return 'twig';
    }

    /**
     * @return Twig_Environment
     */
    public function twig(): Twig_Environment
    {
        if ($this->twig === null) {
            $this->twig = $this->createTwig();
        }
        return $this->twig;
    }

    /**
     * @param string $templateIdent The template identifier to load and render.
     * @param mixed  $context       The rendering context.
     * @return string The rendered template string.
     */
    public function render(string $templateIdent, $context): string
    {
        $arrayContext = json_decode(json_encode($context), true);
        return $this->twig()->render($templateIdent, $arrayContext);
    }

    /**
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    public function renderTemplate(string $templateString, $context): string
    {
        $template = $this->twig()->createTemplate($templateString);
        $arrayContext = json_decode(json_encode($context), true);
        return $template->render($arrayContext);
    }

    /**
     * @return Twig_Environment
     */
    protected function createTwig(): Twig_Environment
    {
        $twig = new Twig_Environment($this->loader(), [
            'cache'     => $this->cache(),
            'charset'   => 'utf-8',
            'debug'     => false
        ]);

        return $twig;
    }

    /**
     * Set the engine's cache implementation.
     *
     * @param  mixed $cache A Twig cache option.
     * @return void
     */
    protected function setCache($cache): void
    {
        /**
         * If NULL is specified, the value is converted to FALSE
         * because Twig internally requires FALSE to disable the cache.
         */
        if ($cache === null) {
            $cache = false;
        }

        parent::setCache($cache);
    }
}
