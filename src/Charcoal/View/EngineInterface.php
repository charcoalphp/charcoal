<?php

namespace Charcoal\View;

/**
* _Engines_ are the actual template renderers for the views.
*
*/
interface EngineInterface
{
    /**
    * @return LoaderInterface
    */
    public function loader();

    /**
    * @return LoaderInterface
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
