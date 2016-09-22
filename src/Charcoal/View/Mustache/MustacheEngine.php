<?php

namespace Charcoal\View\Mustache;

use \Exception;
use \InvalidArgumentException;
use \Traversable;

// Dependency from 'mustache/mustache'
use \Mustache_Engine;

// Intra-module (`charcoal-view`) depentencies
use \Charcoal\View\AbstractEngine;

use \Charcoal\View\Mustache\GenericHelpers;
use \Charcoal\View\Mustache\AssetsHelpers;
use \Charcoal\View\Mustache\HelpersInterface;

/**
 * Mustache view rendering engine.
 */
class MustacheEngine extends AbstractEngine
{
    /**
     * A collection of helpers.
     *
     * @var HelpersCollection|HelpersInterface[] $helpers
     */
    private $helpers;

    /**
     * The renderering framework.
     *
     * @var Mustache_Engine
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
            'helpers'           => $this->helpers()
        ]);

        return $mustache;
    }

    /**
     * Set the engine's helpers.
     *
     * This method overwrites existing helpers.
     *
     * Method accepts a variable list of helpers to merge.
     *
     * @return MustacheEngine Chainable
     */
    public function setHelpers()
    {
        $helpers = func_get_args();
        $helpers = array_map([ $this, 'arrayableHelpers' ], $helpers);

        $this->helpers = call_user_func_array('array_merge', $helpers);

        return $this;
    }

    /**
     * Merge (replacing or adding) helpers.
     *
     * Method accepts a variable list of helpers to merge.
     *
     * @return MustacheEngine Chainable
     */
    public function mergeHelpers()
    {
        $helpers = func_get_args();
        $helpers = array_map([ $this, 'arrayableHelpers' ], $helpers);

        array_unshift($helpers, $this->helpers());

        $this->helpers = call_user_func_array('array_merge', $helpers);

        return $this;
    }

    /**
     * Retrieve the engine's helpers.
     *
     * @return array
     */
    public function helpers()
    {
        if ($this->helpers === null) {
            $this->setHelpers($this->createHelpers());
        }

        return $this->helpers;
    }

    /**
     * Retrieve the engine's default helpers.
     *
     * @return array
     */
    protected function createHelpers()
    {
        $generic = new GenericHelpers();
        $assets  = new AssetsHelpers();

        return array_merge(
            $this->arrayableHelpers($generic),
            $this->arrayableHelpers($assets)
        );
    }

    /**
     * Parse array of helpers from HelpersInterface or Arrayable.
     *
     * @param mixed $helpers An arrayable variable to parse.
     * @throws InvalidArgumentException If the helpers are not arrayable.
     * @return array
     */
    private function arrayableHelpers($helpers)
    {
        if (is_array($helpers)) {
            return $helpers;
        } elseif ($helpers instanceof HelpersInterface) {
            return $helpers->toArray();
        } elseif ($helpers instanceof Traversable) {
            return iterator_to_array($helpers);
        } else {
            throw new InvalidArgumentException('Unable to convert helpers. Must be an array or a traversable object.');
        }
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
