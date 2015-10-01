<?php

namespace Charcoal\View;

/**
* View Engine Interface
*/
interface EngineInterface
{
    /**
    * @return MustacheLoader
    */
    public function loader();

    /**
    * @return MustacheLoader
    */
    public function create_loader();

    /**
    * @param string $template_ident
    * @return string
    */
    public function load_template($template_ident);

    /**
    * @param string $template
    * @param mixed $context
    * @return string
    */
    public function render($template, $context);
}
