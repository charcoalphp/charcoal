<?php

namespace Charcoal\View\Php;

use \InvalidArgumentException;

// Intra-module (`charcoal-view`) depentencies
use \Charcoal\View\AbstractEngine;

/**
 *
 */
class PhpEngine extends AbstractEngine
{
    /**
     * @return string
     */
    public function type()
    {
        return 'php';
    }

    /**
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    public function renderTemplate($templateString, $context)
    {
        // Prevents leaking global variable by forcing anonymous scope
        $render = function($templateString, $context) {
            extract($context);
            return eval('?>'.$templateString);
        };

        ob_start();
        $render($templateString, $context);
        $output = ob_get_clean();

        return $output;
    }
}
