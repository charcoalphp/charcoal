<?php

namespace Charcoal\View;

use \Charcoal\View\ViewInterface as ViewInterface;

/**
* A default (abstract) implementation, as trait, of the ViewableInterface.
*
* There is one additional abstract method: `create_view()`
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
        return $this;
    }
    
    /**
    * @return ViewInterface The object's View.
    */
    public function view()
    {
        if ($this->_view === null) {
            $this->_view = $this->create_view();
        }
        return $this->_view;
    }

    /**
    * @return ViewInterface
    */
    abstract protected function create_view($data = null);

    /**
    * @param string The template to parse and echo. If null, use the object's default.
    */
    public function display($template = null)
    {
        echo $this->render($template);
    }

    /**
    * @param string The template to parse and render. If null, use the object's default.
    * @return string The rendered template.
    */
    public function render($template = null)
    {
        $view_data = [
            'template'=>$template,
            'context'=>$this
        ];
        $this->view()->set_data($view_data);
        return $this->view()->render();
    }

    /**
    * @param string $template_ident The template ident to load and render.
    * @return string The rendered template.
    */
    public function render_template($template_ident)
    {
        $view_data = [
            'context'=>$this
        ];
        $this->view()->set_data($view_data);
        return $this->view()->render_template($template_ident);
    }
}
