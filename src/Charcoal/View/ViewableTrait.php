<?php

namespace Charcoal\View;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\View\AbstractView;
use \Charcoal\View\ViewInterface;

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
    * @var string $template_ident
    */
    protected $_template_ident;

    /**
    * @var ViewInterface $_view
    */
    protected $_view;

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return ViewableTrait Chainable
    */
    public function set_viewable_data(array $data)
    {
        if (isset($data['template_engine']) && $data['template_engine'] !== null) {
            $this->set_template_engine($data['template_engine']);
        }
        if (isset($data['template_ident']) && $data['template_ident'] !== null) {
            $this->set_template_ident($data['template_ident']);
        }
        return $this;
    }

    /**
    * @param string $engine
    * @throws InvalidArgumentException
    * @return ViewableTrait Chainable
    */
    public function set_template_engine($engine)
    {
        if (!is_string($engine)) {
            throw new InvalidArgumentException(
                'Engine must be a string.'
            );
        }
        $this->_template_engine = $engine;
        return $this;
    }

    /**
    * @return string
    */
    public function template_engine()
    {
        if ($this->_template_engine === null) {
            $this->_template_engine = AbstractView::DEFAULT_ENGINE;
        }
        return $this->_template_engine;
    }

    /**
    * @param string $ident
    * @throws InvalidArgumentException
    * @return ViewableTrait Chainable
    */
    public function set_template_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Template ident must be a string.'
            );
        }
        $this->_template_ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function template_ident()
    {
        return $this->_template_ident;
    }

    /**
    * @param ViewInterface|array $view
    * @throws InvalidArgumentException
    * @return mixed Chainable
    */
    public function set_view($view)
    {
        if (is_array($view)) {
            $this->_view = $this->create_view($view);
        } elseif (($view instanceof ViewInterface)) {
            $this->_view = $view;
        } else {
            throw new InvalidArgumentException(
                'View must be an array or a ViewInterface object.'
            );
        }
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
    * @param mixed $data
    * @return ViewInterface
    */
    abstract protected function create_view($data = null);

    /**
    * @param string $template The template to parse and echo. If null, use the object's default.
    * @return void
    */
    public function display($template = null)
    {
        echo $this->render($template);
    }

    /**
    * @param string $template The template to parse and render. If null, use the object's default.
    * @return string The rendered template.
    */
    public function render($template = null)
    {
        $view_data = [
            'template' => $template,
            'context'  => $this
        ];
        $this->view()->set_data($view_data);
        return $this->view()->render();
    }

    /**
    * @param string $template_ident The template ident to load and render.
    * @return string The rendered template.
    */
    public function render_template($template_ident = null)
    {
        if ($template_ident === null) {
            $template_ident = $this->template_ident();
        }

        $view_data = [
            'context' => $this
        ];
        $this->view()->set_data($view_data);
        return $this->view()->render_template($template_ident);
    }
}
