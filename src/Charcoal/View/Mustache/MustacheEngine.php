<?php

namespace Charcoal\View\Mustache;

// 3rd-party libraries (`mustache/mustache`) dependencies
use \Mustache_Engine;

// Intra-module (`charcoal-view`) depentencies
use \Charcoal\View\AbstractEngine;
use \Charcoal\View\LoaderInterface;

// Local namespace dependencies
use \Charcoal\View\Mustache\MustacheLoader;

/**
 * Mustache view rendering engine.
 */
class MustacheEngine extends AbstractEngine
{
    /**
     * @var GenericHelper $helper
     */
    private $helper;

    /**
     * @var Mustache_Engine $mustache
     */
    private $mustache;

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
            $this->setLogger($data['logger']);
        }

        if (isset($data['loader'])) {
            $this->setLoader($data['loader']);
        }
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'mustache';
    }

    /**
     * @return Mustache_Engine
     */
    public function mustache()
    {
        if ($this->mustache === null) {
            $this->mustache = $this->createMustache();
        }
        return $this->mustache;
    }

    /**
     * @return Mustache_Engine
     */
    public function createMustache()
    {
        $mustache = new Mustache_Engine([
            'cache'             => 'mustache_cache',

            'loader'            => $this->loader(),
            'partials_loader'   => $this->loader(),

            'strict_callables'  => true,

            'helpers'           => $this->helper()
        ]);
        return $mustache;
    }

    /**
     * @return LoaderInterface
     */
    public function createLoader()
    {
        $loader = new MustacheLoader([
            'logger' => $this->logger
        ]);
        return $loader;
    }

    /**
     * @param mixed $helper
     * @return MustacheEngine Chainable
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;
        return $this;
    }

    /**
     * @return MustacheLoader
     */
    public function helper()
    {
        if ($this->helper === null) {
            $this->helper = $this->createHelper();
        }
        return $this->helper;
    }

    /**
     * @return MustacheLoader
     */
    public function createHelper()
    {
        $helper = new GenericHelper();
        return $helper;
    }

    /**
     * @param string $template
     * @param mixed  $context
     * @return string
     */
    public function render($template, $context)
    {
        return $this->mustache()->render($template, $context);
    }
}
