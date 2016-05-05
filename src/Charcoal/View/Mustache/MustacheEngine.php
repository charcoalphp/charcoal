<?php

namespace Charcoal\View\Mustache;

// 3rd-party libraries (`mustache/mustache`) dependencies
use \Mustache_Engine;

// Intra-module (`charcoal-view`) depentencies
use \Charcoal\View\AbstractEngine;

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
     * ## Required parameters:
     * - `logger` a PSR-3 logger
     *
     * ## Optional parameters:
     * - `loader` a Loader object
     *
     * @param array $data Engine dependencie.
     */
    public function __construct(array $data)
    {
        $this->setLogger($data['logger']);

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
    protected function mustache()
    {
        if ($this->mustache === null) {
            $this->mustache = $this->createMustache();
        }
        return $this->mustache;
    }

    /**
     * @return Mustache_Engine
     */
    protected function createMustache()
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
     * @param mixed $helper The helper.
     * @return MustacheEngine Chainable
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;
        return $this;
    }

    /**
     * @return GenericHelper
     */
    public function helper()
    {
        if ($this->helper === null) {
            $this->helper = $this->createHelper();
        }
        return $this->helper;
    }

    /**
     * @return GenericHelper
     */
    protected function createHelper()
    {
        $helper = new GenericHelper();
        return $helper;
    }

    /**
     * @param string $templateIdent The template identifier to load and render.
     * @param mixed  $context       The rendering context.
     * @return string The rendered template string.
     */
    public function render($templateIdent, $context)
    {
        return $this->mustache()->render($templateIdent, $context);
    }

    /**
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    public function renderTemplate($templateString, $context)
    {
        return $this->mustache()->render($templateString, $context);
    }
}
