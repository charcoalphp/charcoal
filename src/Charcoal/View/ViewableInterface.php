<?php

namespace Charcoal\View;

/**
 * Defines an object as viewable, and therefore can be rendered.
 */
interface ViewableInterface
{
    /**
     * Render the viewable object.
     *
     * @return string
     */
    public function __toString();

    /**
     * Set the view engine type (identifier).
     *
     * @param string $engineIdent The rendering engine identifier.
     * @return ViewableInterface Chainable
     */
    public function setTemplateEngine($engineIdent);

    /**
     * Retrieve the view engine type (identifier).
     *
     * @return string Returns either "mustache", "php", "php-mustache" or "twig".
     */
    public function templateEngine();

    /**
     * Set the viewable object's template identifier.
     *
     * Usually, a path to a file containing the template to be rendered at runtime.
     *
     * @param string $ident The template ID.
     * @return ViewableInterface Chainable
     */
    public function setTemplateIdent($ident);

    /**
     * Retrieve the viewable object's template identifier.
     *
     * @return string
     */
    public function templateIdent();

    /**
     * Set the renderable view.
     *
     * @param ViewInterface|array $view The view instance to use to render.
     * @return ViewableInterface Chainable
     */
    public function setView(ViewInterface $view);

    /**
     * Retrieve the renderable view.
     *
     * @return ViewInterface The object's View instance.
     */
    public function view();

    /**
     * Render the template by the given identifier.
     *
     * Usually, a path to a file containing the template to be rendered at runtime.
     *
     * @param string $templateIdent The template to load, parse, and render.
     *     If NULL, will use the object's previously set template identifier.
     * @return string The rendered template.
     */
    public function render($templateIdent = null);

    /**
     * Render the given template from string.
     *
     * @param string $templateString The template  to render from string.
     * @return string The rendered template.
     */
    public function renderTemplate($templateString);
}
