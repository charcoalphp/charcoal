<?php

namespace Charcoal\View\Twig;

// 3rd-party libraries (`twigphp/twig`) dependencies
use Twig_Environment;

// Intra-module (`charcoal-view`) depentencies
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
    public function type()
    {
        return 'twig';
    }

    /**
     * @return Twig_Environment
     */
    public function twig()
    {
        if ($this->twig === null) {
            $this->twig = $this->createTwig();
        }
        return $this->twig;
    }

    /**
     * @return Twig_Environment
     */
    protected function createTwig()
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
    protected function setCache($cache)
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

    /**
     * @param string $templateIdent The template identifier to load and render.
     * @param mixed  $context       The rendering context.
     * @return string The rendered template string.
     */
    public function render($templateIdent, $context)
    {
        $arrayContext = json_decode(json_encode($context), true);
        return $this->twig()->render($templateIdent, $arrayContext);
    }

    /**
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    public function renderTemplate($templateString, $context)
    {
        $template = $this->twig()->createTemplate($templateString);
        $arrayContext = json_decode(json_encode($context), true);
        return $template->render($arrayContext);
    }
}
