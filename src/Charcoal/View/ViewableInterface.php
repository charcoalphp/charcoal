<?php

namespace Charcoal\View;

use \Charcoal\View\ViewInterface as ViewInterface;

interface ViewableInterface
{
    /**
    * @param ViewInterface $view
    * @return ViewableInterface Chainable
    */
    public function set_view(ViewInterface $view);
    
    /**
    * @return ViewInterface The object's View.
    */
    public function view();

    /**
    * @param string The template to parse and echo. If null, use the object's default.
    */
    public function display($template=null);

    /**
    * @param string The template to parse and render. If null, use the object's default.
    * @return string The rendered template.
    */
    public function render($template=null);

    /**
    * @param string $template_ident The template ident to load and render.
    * @return string The rendered template.
    */
    public function render_template($template_ident);
}
