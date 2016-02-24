<?php

namespace Charcoal\View;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Module `charcoal-config` dependencies
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\View\Mustache\MustacheEngine;
use \Charcoal\View\Php\PhpEngine;
use \Charcoal\View\PhpMustache\PhpMustacheEngine;
use \Charcoal\View\Twig\TwigEngine;
use \Charcoal\View\ViewInterface;

/**
 * Base abstract class for _View_ interfaces, implements `ViewInterface`.
 *
 * Also implements the `ConfigurableInterface`
 */
abstract class AbstractView implements
    ConfigurableInterface,
    LoggerAwareInterface,
    ViewInterface
{
    use LoggerAwareTrait;
    use ConfigurableTrait;

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
     * A view object can simply be echoed to be rendered.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->renderTemplate();
    }

    /**
     * > ConfigurableTrait . createConfig()
     *
     * @param array $data
     * @return ViewConfig
     */
    public function createConfig(array $data = null)
    {
        $config = new ViewConfig();
        if ($data !== null) {
            $config->merge($data);
        }
        return $config;
    }

    /**
     * Set the engine (`EngineInterface`) dependency.
     *
     * @param EngineInterface $engine The
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
     * @param string $templateIdent
     * @throws InvalidArgumentException if the provided argument is not a string
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
     * @param string $template
     * @throws InvalidArgumentException if the provided argument is not a string
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
     * @param string $templateIdent
     * @throws InvalidArgumentException
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
     * @return mixed
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * @param string $templateIdent Optional. The template identifier to load. If none then use instance's.
     * @param mixed  $context The rendering context.
     * @return string The rendered template string.
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
     * @param string $templateString Optional. The template string to render. If none then load instance's.
     * @param mixed  $context The rendering context.
     * @return string The rendered template string.
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
