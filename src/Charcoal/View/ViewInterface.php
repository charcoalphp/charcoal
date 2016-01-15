<?php

namespace Charcoal\View;

/**
 * View Interface
 */
interface ViewInterface
{
    /**
     * Set the engine type
     *
     * @param string $engineType
     * @throws InvalidArgumentException
     * @return AbstractView Chainable
     */
    public function setEngineType($engineType);

    /**
     * @return string
     */
    public function engineType();

    /**
     * @param EngineInterface $engine
     */
    public function setEngine(EngineInterface $engine);

    /**
     * @return EngineInterface
     */
    public function engine();

    /**
     * @return EngineInterface
     */
    public function createEngine();

    /**
     * @param string $template_ident
     * @throws InvalidArgumentException if the provided argument is not a string
     * @return AbstractView Chainable
     */
    public function setTemplateIdent($template_ident);

    /**
     * @return string
     */
    public function templateIdent();

    /**
     * @param string $template
     * @throws InvalidArgumentException if the provided argument is not a string
     * @return AbstractView Chainable
     */
    public function setTemplate($template);

    /**
     * @return string
     */
    public function template();

    /**
     * @param string $template_ident
     * @throws InvalidArgumentException
     * @return string
     */
    public function loadTemplate($template_ident = null);

    /**
     * @param mixed $context
     * @return AbstractView Chainable
     */
    public function setContext($context);

    /**
     * @return mixed
     */
    public function context();

    /**
     * @param string $template The template identifier, to load and render.
     * @param mixed  $context Template context
     * @return string
     */
    public function render($template_ident = null, $context = null);

    /**
     * @param string $template_string The full template string to render.
     * @param mixed  $context
     * @return string
     */
    public function renderTemplate($template_string = null, $context = null);
}
