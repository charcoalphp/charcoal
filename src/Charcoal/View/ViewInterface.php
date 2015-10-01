<?php

namespace Charcoal\View;

/**
* View Interface
*/
interface ViewInterface
{

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
