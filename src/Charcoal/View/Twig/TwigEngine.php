<?php

namespace Charcoal\View\Twig;

// 3rd-party libraries (`twigphp/twig`) dependencies
use \Twig_Environment;

// Intra-module (`charcoal-view`) depentencies
use \Charcoal\View\AbstractEngine;

/**
 *
 */
class TwigEngine extends AbstractEngine
{
    /**
     * @var Twig_Environment $twig
     */
    private $twig;

    /**
     * Build the object with an array of dependencies.
     *
     * ## Optional parameters:
     * - `loader` a Loader object
     * - `logger` a PSR logger
     *
     * @param array $data
     */
    public function __construct($data)
    {

        $this->setLogger($data['logger']);

        if (isset($data['loader'])) {
            $this->set_loader($data['loader']);
        }
    }

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
            'cache'     => 'twig_cache',
            'charset'   => 'utf-8',
            'debug'     => false
        ]);

        return $twig;
    }

    /**
     * @param string $templateIdent
     * @param mixed  $context
     * @return string
     */
    public function render($templateIdent, $context)
    {
        return $this->twig()->render($templateIdent, ['data'=>$context]);
    }

    /**
     * @param string $templateString
     * @param mixed  $context
     * @return string
     */
    public function renderTemplate($templateString, $context)
    {
        return $this->twig()->render($templateString, ['data'=>$context]);
    }
}
