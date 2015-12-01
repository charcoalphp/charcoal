<?php

namespace Charcoal\View;

/**
 * View Interface
 */
interface ViewInterface
{
    /**
     * Set the engine type
     *
     * @param string $engine_type
     * @throws InvalidArgumentException
     * @return AbstractView Chainable
     */
    public function set_engine_type($engine_type);

    /**
     * @return string
     */
    public function engine_type();

    /**
     * @param EngineInterface $engine
     */
    public function set_engine(EngineInterface $engine);

    /**
     * @return EngineInterface
     */
    public function engine();

    /**
     * @return EngineInterface
     */
    public function create_engine();

    /**
     * @param string $template_ident
     * @throws InvalidArgumentException if the provided argument is not a string
     * @return AbstractView Chainable
     */
    public function set_template_ident($template_ident);

    /**
     * @return string
     */
    public function template_ident();

    /**
     * @param string $template
     * @throws InvalidArgumentException if the provided argument is not a string
     * @return AbstractView Chainable
     */
    public function set_template($template);

    /**
     * @return string
     */
    public function template();

    /**
     * @param string $template_ident
     * @throws InvalidArgumentException
     * @return string
     */
    public function load_template($template_ident = null);

    /**
     * @param mixed $context
     * @return AbstractView Chainable
     */
    public function set_context($context);

    /**
     * @return mixed
     */
    public function context();

    /**
     * @param string $template
     * @param mixed  $context
     * @return string
     */
    public function render($template = null, $context = null);

    /**
     * @param string $template_ident
     * @param mixed  $context
     * @return string
     */
    public function render_template($template_ident, $context = null);
}
