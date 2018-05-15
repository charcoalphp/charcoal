<?php

namespace Charcoal\View;

use InvalidArgumentException;

// From 'charcoal-view'
use Charcoal\View\ViewInterface;

/**
 * Implementation, as trait, of the {@see \Charcoal\View\ViewableInterface}.
 */
trait ViewableTrait
{
    /**
     * The object's template identifier.
     *
     * @var string
     */
    private $templateIdent;

    /**
     * The context for the {@see self::$view} to render templates.
     *
     * @var ViewableInterface
     */
    private $viewController;

    /**
     * The renderable view.
     *
     * @var ViewInterface
     */
    private $view;

    /**
     * Render the viewable object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Set the template identifier for this viewable object.
     *
     * Usually, a path to a file containing the template to be rendered at runtime.
     *
     * @param string $templateIdent The template ID.
     * @throws InvalidArgumentException If the template identifier is not a string.
     * @return self
     */
    public function setTemplateIdent($templateIdent)
    {
        if (!is_string($templateIdent)) {
            throw new InvalidArgumentException(
                'Template identifier must be a string.'
            );
        }

        $this->templateIdent = $templateIdent;

        return $this;
    }

    /**
     * Retrieve the template identifier for this viewable object.
     *
     * @return string
     */
    public function templateIdent()
    {
        return $this->templateIdent;
    }

    /**
     * Set the renderable view.
     *
     * @param ViewInterface|array $view The view instance to use to render.
     * @throws InvalidArgumentException If the view parameter is not an array or a View object.
     * @return self
     */
    public function setView(ViewInterface $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Retrieve the renderable view.
     *
     * @return ViewInterface The object's View instance.
     */
    public function view()
    {
        return $this->view;
    }

    /**
     * Render the template by the given identifier.
     *
     * Usually, a path to a file containing the template to be rendered at runtime.
     *
     * @param string $templateIdent The template to load, parse, and render.
     *     If NULL, will use the object's previously set template identifier.
     * @return string The rendered template.
     */
    public function render($templateIdent = null)
    {
        if ($templateIdent === null) {
            $templateIdent = $this->templateIdent();
        }

        return $this->view()->render($templateIdent, $this->viewController());
    }

    /**
     * Render the given template from string.
     *
     * @param string $templateString The template  to render from string.
     * @return string The rendered template.
     */
    public function renderTemplate($templateString)
    {
        return $this->view()->renderTemplate($templateString, $this->viewController());
    }

    /**
     * Set a view controller for the template's context.
     *
     * @param ViewableInterface|object|array|null $controller A view controller to use when rendering.
     * @throws InvalidArgumentException If the controller is invalid.
     * @return self
     */
    public function setViewController($controller)
    {
        if (is_scalar($controller) || is_resource($controller)) {
            throw new InvalidArgumentException(
                'View controller must be an object, null or an array'
            );
        }

        $this->viewController = $controller;

        return $this;
    }

    /**
     * Retrieve a view controller for the template's context.
     *
     * If no controller has been defined, it will return itself.
     *
     * @return mixed
     */
    public function viewController()
    {
        if ($this->viewController === null) {
            return $this;
        }

        return $this->viewController;
    }

    /**
     * @param string      $varName       The name of the variable to set this template unto.
     * @param string|null $templateIdent The "dynamic template" to set. null to clear.
     * @return void
     */
    public function setDynamicTemplate($varName, $templateIdent)
    {
        $this->view()->setDynamicTemplate($varName, $templateIdent);
    }
}
