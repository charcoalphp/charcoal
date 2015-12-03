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
    public function set_template_engine($engine);

    /**
     * @return string The template engine (`mustache`, `php`, `php-mustache` or `twig`)
     */
    public function template_engine();

    /**
     * @param string $ident
     * @return ViewableInterface Chainable
     */
    public function set_template_ident($ident);

    /**
     * @return string
     */
    public function template_ident();

    /**
     * @param ViewInterface|array $view
     * @return ViewableInterface Chainable
     */
    public function set_view($view);

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
     * @param string $template_ident The template ident to load and render.
     * @return string The rendered template.
     */
    public function render_template($template_ident);
}
