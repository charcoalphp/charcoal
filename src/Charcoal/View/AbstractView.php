<?php

namespace Charcoal\View;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Local namespace dependencie
use \Charcoal\View\EngineInterface;
use \Charcoal\View\ViewInterface;

/**
 * Base abstract class for _View_ interfaces, implements `ViewInterface`.
 *
 * Also implements the `ConfigurableInterface`
 */
abstract class AbstractView implements
    LoggerAwareInterface,
    ViewInterface
{
    use LoggerAwareTrait;

    /**
     * @var string $templateIdent
     */
    private $templateIdent;

    /**
     * @var string $template
     */
    private $template;

    /**
     * @var EngineInterface $engine
     */
    private $engine;

    /**
     * @var mixed $context
     */
    private $context;

    /**
     * Build the object with an array of dependencies.
     *
     * ## Parameters:
     * - `logger` a PSR-3 logger
     *
     * @param array $data View class dependencies.
     * @throws InvalidArgumentException If required parameters are missing.
     */
    public function __construct(array $data)
    {
        $this->setLogger($data['logger']);
    }

    /**
     * Set the engine (`EngineInterface`) dependency.
     *
     * @param EngineInterface $engine The rendering engine.
     * @return ViewInterface Chainable
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * Get the view's rendering engine instance.
     *
     * @throws Exception If the engine is accessed before it was properly set.
     * @return EngineInterface
     */
    public function engine()
    {
        if (!isset($this->engine)) {
            throw new Exception(
                'Engine must first be set on view, with `setEngine()`'
            );
        }
        return $this->engine;
    }

    /**
     * @param string $templateIdent The template ident.
     * @throws InvalidArgumentException If the provided argument is not a string.
     * @return AbstractView Chainable
     */
    public function setTemplateIdent($templateIdent)
    {
        if (!is_string($templateIdent)) {
            throw new InvalidArgumentException(
                'Template ident must be a string.'
            );
        }

        $this->templateIdent = $templateIdent;
        return $this;
    }

    /**
     * @return string
     */
    public function templateIdent()
    {
        return $this->templateIdent;
    }

    /**
     * @param string $template The template string.
     * @throws InvalidArgumentException If the provided argument is not a string.
     * @return AbstractView Chainable
     */
    public function setTemplate($template)
    {
        if (!is_string($template)) {
            throw new InvalidArgumentException(
                'Template must be a string.'
            );
        }

        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function template()
    {
        if ($this->template === null) {
            return $this->loadTemplate();
        }

        return $this->template;
    }

    /**
     * @param string $templateIdent The template identifier to load..
     * @throws InvalidArgumentException If the template ident is not a string.
     * @return string
     */
    public function loadTemplate($templateIdent = null)
    {
        if ($templateIdent === null) {
            $templateIdent = $this->templateIdent();
        }
        if (!is_string($templateIdent)) {
            throw new InvalidArgumentException(
                'Template ident must be a string'
            );
        }
        if (!$templateIdent) {
            return '';
        }
        $template = $this->engine()->loadTemplate($templateIdent);
        return $template;
    }

    /**
     * Set the rendering context ("view controller").
     *
     * @param mixed $context The context / view controller to render the template with.
     * @return AbstractView Chainable
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get the rendering context ("view controller").
     *
     * @return mixed
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * Load a template (from identifier) and render it.
     *
     * @param string $templateIdent The template identifier, to load and render.
     * @param mixed  $context       The view controller (rendering context).
     * @return string
     */
    public function render($templateIdent = null, $context = null)
    {
        if ($templateIdent === null) {
            $templateIdent = $this->templateIdent();
        }
        if ($context === null) {
            $context = $this->context();
        }
        return $this->engine()->render($templateIdent, $context);
    }

    /**
     * Render a template (from string).
     *
     * @param string $templateString The full template string to render. If none specified, used
     * @param mixed  $context        The view controller (rendering context).
     * @return string
     */
    public function renderTemplate($templateString = null, $context = null)
    {
        if ($templateString === null) {
            $templateString = $this->template();
        }
        if ($context === null) {
            $context = $this->context();
        }
        return $this->engine()->render($templateString, $context);
    }
}
