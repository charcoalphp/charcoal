<?php

namespace Charcoal\View;

/**
 * Viewable objects have a view, and therefore can be rendered.
 */
interface ViewableInterface
{

    /**
     * @return string
     */
    public function __toString();

    /**
     * Set the type of view engine to use for this vi
     *
     * @param string $engine
     * @return ViewableInterface Chainable
     */
    public function setTemplateEngine($engine);

    /**
     * @return string The template engine (`mustache`, `php`, `php-mustache` or `twig`)
     */
    public function templateEngine();

    /**
     * @param string $ident
     * @return ViewableInterface Chainable
     */
    public function setTemplateIdent($ident);

    /**
     * @return string
     */
    public function templateIdent();

    /**
     * @param ViewInterface|array $view
     * @return ViewableInterface Chainable
     */
    public function setView($view);

    /**
     * @return ViewInterface The object's View.
     */
    public function view();

    /**
     * @param string $template The template to parse and render. If null, use the object's default.
     * @return string The rendered template.
     */
    public function render($template = null);

    /**
     * @param string $templateIdent The template ident to load and render.
     * @return string The rendered template.
     */
    public function renderTemplate($templateIdent);
}
