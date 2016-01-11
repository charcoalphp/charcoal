<?php

namespace Charcoal\View;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Local namespace dependencies
use \Charcoal\View\EngineInterface;
use \Charcoal\View\LoaderInterface;

/**
 * Default implementation, as abstract class, of the `EngineInterface`.
 *
 * View Engines are comprised of 2 things:
 * - A template loader, wich is a `LoaderInterfaceObject`
 *   - Set with `set_loader()` / Get with `loader()`
 *   - Provides `loadtemplate()` method
 * - A `render()` method, which takes a $template and a $context arguments
 *
 * > Engines implements the `LoggerAwareInterface`. A logger can be accessed with the `logger()` method.
 */
abstract class AbstractEngine implements
    EngineInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var LoaderInterface $loader
     */
    private $loader;

    /**
     * @return string
     */
    abstract public function type();

    /**
     * @param LoaderInterface $loader A loader instance.
     * @return MustacheEngine Chainable
     */
    public function set_loader(LoaderInterface $loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * @return LoaderInterface
     */
    public function loader()
    {
        if ($this->loader === null) {
            $this->loader = $this->create_loader();
        }
        return $this->loader;
    }

    /**
     * @return LoaderInterface
     */
    abstract public function create_loader();

    /**
     * Delegates template loading to the engine's Loader object.
     *
     * @param string $template_ident
     * @return string
     */
    public function load_template($template_ident)
    {
        return $this->loader()->load($template_ident);
    }

    /**
     * @param string $template
     * @param mixed  $context
     * @return string
     */
    abstract public function render($template, $context);
}
