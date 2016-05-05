<?php

namespace Charcoal\View;

// PHP dependencies
use \Exception;

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
     * @return EngineInterface Chainable
     */
    public function setLoader(LoaderInterface $loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * @throws Exception If the loader getter is called before it was set.
     * @return LoaderInterface
     */
    protected function loader()
    {
        if ($this->loader === null) {
            throw new Exception(
                'Loader must first be passed with `setLoader()`'
            );
        }
        return $this->loader;
    }

    /**
     * Delegates template loading to the engine's Loader object.
     *
     * @param string $templateIdent The template identifier to load.
     * @return string The template string, loaded from identifier.
     */
    public function loadTemplate($templateIdent)
    {
        return $this->loader()->load($templateIdent);
    }

    /**
     * @param string $templateIdent The template identifier to load and render.
     * @param mixed  $context       The rendering context.
     * @return string The rendered template string.
     */
    public function render($templateIdent, $context)
    {
        $template = $this->loadTemplate($templateIdent);
        return $this->renderTemplate($template, $context);
    }

    /**
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    abstract public function renderTemplate($templateString, $context);
}
