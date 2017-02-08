<?php

namespace Charcoal\View\Mustache;

use InvalidArgumentException;
use Traversable;

// Dependency from 'mustache/mustache'
use Mustache_Engine;

// Intra-module (`charcoal-view`) depentencies
use Charcoal\View\AbstractEngine;

use Charcoal\View\Mustache\HelpersInterface;

/**
 * Mustache view rendering engine.
 */
class MustacheEngine extends AbstractEngine
{
    /**
     * A collection of helpers.
     *
     * @var array
     */
    private $helpers = [];

    /**
     * The renderering framework.
     *
     * @var Mustache_Engine
     */
    private $mustache;

    /**
     * @return string
     */
    public function type()
    {
        return 'mustache';
    }

    /**
     * Build the Mustache Engine with an array of dependencies.
     *
     * @param array $data Engine dependencie.
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        if (isset($data['helpers'])) {
            $this->setHelpers($data['helpers']);
        }
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
     * @param  array|Traversable|HelpersInterface $helpers Mustache helpers.
     * @throws InvalidArgumentException If the given helper(s) are invalid.
     * @return MustacheEngine Chainable
     */
    public function setHelpers($helpers)
    {
        if ($helpers instanceof HelpersInterface) {
            $helpers = $helpers->toArray();
        }

        if (!is_array($helpers) && !$helpers instanceof Traversable) {
            throw new InvalidArgumentException(sprintf(
                'setHelpers expects an array of helpers, received %s',
                (is_object($helpers) ? get_class($helpers) : gettype($helpers))
            ));
        }

        $this->helpers = [];
        foreach ($helpers as $name => $helper) {
            $this->addHelper($name, $helper);
        }

        return $this;
    }

    /**
     * Merge (replacing or adding) the engine's helpers.
     *
     * @param  array|Traversable|HelpersInterface $helpers Mustache helpers.
     * @throws InvalidArgumentException If the given helper(s) are invalid.
     * @return MustacheEngine Chainable
     */
    public function mergeHelpers($helpers)
    {
        if ($helpers instanceof HelpersInterface) {
            $helpers = $helpers->toArray();
        }

        if (!is_array($helpers) && !$helpers instanceof Traversable) {
            throw new InvalidArgumentException(sprintf(
                'mergeHelpers expects an array of helpers, received %s',
                (is_object($helpers) ? get_class($helpers) : gettype($helpers))
            ));
        }

        foreach ($helpers as $name => $helper) {
            $this->addHelper($name, $helper);
        }

        return $this;
    }

    /**
     * Add a helper.
     *
     * @param  string $name   The tag name.
     * @param  mixed  $helper The tag value.
     * @return MustacheEngine Chainable
     */
    public function addHelper($name, $helper)
    {
        $this->helpers[$name] = $helper;

        return $this;
    }

    /**
     * Retrieve the engine's helpers.
     *
     * @return array
     */
    public function helpers()
    {
        return $this->helpers;
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
