<?php

namespace Charcoal\View;

use \Charcoal\View\ViewInterface as ViewInterface;

/**
* A default (abstract) implementation, as trait, of the ViewableInterface
*
*/
trait ViewableTrait
{
    /**
    * @var ViewInterface
    */
    protected $_view;

    /**
    * @param ViewInterface $view
    * @return mixed Chainable
    */
    public function set_view(ViewInterface $view)
    {
        $this->_view = $view;
    }
    
    /**
    * @return ViewInterface The object's View.
    */
    public function view()
    {
        return $this->_view;
    }

    /**
    * @param string The template to parse and echo. If null, use the object's default.
    */
    public function display($template=null)
    {
        echo $this->render($template);
    }

    /**
    * @param string The template to parse and render. If null, use the object's default.
    * @return string The rendered template.
    */
    abstract public function render($template=null);

    /**
    * @param string $template_ident The template ident to load and render.
    * @return string The rendered template.
    */
    abstract public function render_template($template_ident);
}
