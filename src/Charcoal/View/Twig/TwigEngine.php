<?php

namespace Charcoal\View\Twig;

// 3rd-party libraries (`twigphp/twig`) dependencies
use \Twig_Environment;

// Intra-module (`charcoal-view`) depentencies
use \Charcoal\View\AbstractEngine;
use \Charcoal\View\LoaderInterface;

// Local namespace dependencies
use \Charcoal\View\Twig\TwigLoader;

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
        if (isset($data['logger'])) {
            $this->set_logger($data['logger']);
        }
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
            $this->twig = $this->create_twig();
        }
        return $this->twig;
    }
    
    /**
     * @return Twig_Environment
     */
    public function create_twig()
    {
        $loader = new Twig_Loader_Filesystem($this->loader()->paths());
        $twig = new Twig_Environment($loader, [
            'cache' => 'twig_cache',
            'charset'   => 'utf-8',
            'debug' => false
        ]);

        return $mustache;
    }

    /**
     * @return LoaderInterface
     */
    public function create_loader()
    {
        $loader = new TwigLoader([
            'search_path'=>[]
        ]);
        return $loader;
    }

    /**
     * @param string $template
     * @param mixed  $context
     * @return string
     */
    public function render($template, $context)
    {
        
    }
}
