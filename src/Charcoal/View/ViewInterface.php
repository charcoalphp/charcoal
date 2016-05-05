<?php

namespace Charcoal\View;

/**
 * View Interface
 */
interface ViewInterface
{
    /**
     * @param EngineInterface $engine The rendering engine.
     * @return ViewInterface Chainable
     */
    public function setEngine(EngineInterface $engine);

    /**
     * @return EngineInterface
     */
    public function engine();

    /**
     * @param string $templateIdent The template ident.
     * @return AbstractView Chainable
     */
    public function setTemplateIdent($templateIdent);

    /**
     * @return string
     */
    public function templateIdent();

    /**
     * @param string $template The template string.
     * @return AbstractView Chainable
     */
    public function setTemplate($template);

    /**
     * @return string
     */
    public function template();

    /**
     * @param string $templateIdent The template identifier to load.
     * @return string The loaded template string.
     */
    public function loadTemplate($templateIdent = null);

    /**
     * Set the rendering context ("view controller").
     *
     * @param mixed $context The view controller (context).
     * @return AbstractView Chainable
     */
    public function setContext($context);

    /**
     * @return mixed
     */
    public function context();

    /**
     * @param string $templateIdent The template identifier, to load and render.
     * @param mixed  $context       The view controller (rendering context).
     * @return string
     */
    public function render($templateIdent = null, $context = null);

    /**
     * @param string $templateString The full template string to render.
     * @param mixed  $context        The view controller (rendering context).
     * @return string
     */
    public function renderTemplate($templateString = null, $context = null);
}
