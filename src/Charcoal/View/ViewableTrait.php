<?php

namespace Charcoal\View;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\View\AbstractView as AbstractView;
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
    * @var string $_template_engine
    */
    protected $_template_engine;

    /**
    * @var ViewInterface $_view
    */
    protected $_view;

    public function set_viewable_data($data)
    {
        if (isset($data['template_engine']) && $data['template_engine'] !== null) {
            $this->set_template_engine($data['template_engine']);
        }
        return $this;
    }

    public function set_template_engine($engine)
    {
        if (!is_string($engine)) {
            throw new InvalidArgumentException('Engine must be a string');
        }
        $this->_template_engine = $engine;
        return $this;
    }

    public function template_engine()
    {
        if ($this->_template_engine === null) {
            $this->_template_engine = AbstractView::DEFAULT_ENGINE;
        }
        return $this->_template_engine;
    }

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
