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
     * @var string $template_engine
     */
    private $template_engine;

    /**
     * @var string $template_ident
     */
    private $template_ident;

    /**
     * @var ViewInterface $view
     */
    private $view;

    /**
     * Viewable objects can be rendered with `print` or `echo`
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
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
                'Template engine must be a string.'
            );
        }
        $this->template_engine = $engine;
        return $this;
    }

    /**
     * Return the view engine type (identifier).
     *
     * Can be "mustache", "php", "php-mustache" or "twig".
     *
     * @return string
     */
    public function template_engine()
    {
        if ($this->template_engine === null) {
            $this->template_engine = AbstractView::DEFAULT_ENGINE;
        }
        return $this->template_engine;
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
        $this->template_ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function template_ident()
    {
        return $this->template_ident;
    }

    /**
     * @param ViewInterface|array $view
     * @throws InvalidArgumentException If the view parameter is not an array or a View object.
     * @return ViewableInterface Chainable
     */
    public function set_view($view)
    {
        if (is_array($view)) {
            $this->view = $this->create_view($view);
        } elseif (($view instanceof ViewInterface)) {
            $this->view = $view;
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
        if ($this->view === null) {
            return $this->create_view();
        }
        return $this->view;
    }

    /**
     * @param mixed $data
     * @return ViewInterface
     */
    abstract public function create_view($data = null);

    /**
     * @param string $template The template to parse and render. If null, use the object's default.
     * @return string The rendered template.
     */
    public function render($template = null)
    {
        return $this->view()->render($template, $this->view_controller());
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
        return $this->view()->render_template($template_ident, $this->view_controller());
    }

    /**
     * Return a viewableinterface
     * @return ViewableInterface [description]
     */
    public function view_controller()
    {
        return $this;
    }
}
