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
    * @param string
    * @return string
    */
    public function load_template($template_ident);

    /**
    * @var mixed $context
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
    *
    */
    public function set_controller(ViewControllerInterface $controller);

    /**
    *
    */
    public function controller();

    /**
    *
    */
    public function render($template=null, $context=null);

    /**
    *
    */
    public function render_template($template_ident='', $context=null);
}
