<?php

namespace Charcoal\View;

/**
* View Interface
*/
interface ViewInterface
{
    /**
    * @param array $data
    * @return ViewInterface Chainable
    */
    public function set_data($data);

    /**
    * @param string $template
    * @return ViewInterface Chainable
    */
    public function set_template($template);

    /**
    * @return string
    */
    public function template();

    /**
    * @param string $template_ident
    * @return string
    */
    public function load_template($template_ident);

    /**
    * @param mixed $context
    * @return ViewInterface Chainable
    */
    public function set_context($context);

    /**
    * @return mixed
    */
    public function context();

    /**
    * @param string $context_ident
    * @return mixed
    */
    public function load_context($context_ident);

    /**
    * @param ViewControllerInterface $controller
    * @return ViewInterface Chainable
    */
    public function set_controller(ViewControllerInterface $controller);

    /**
    * @return ViewControllerInterface
    */
    public function controller();

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
    public function render_template($template_ident = '', $context = null);
}
