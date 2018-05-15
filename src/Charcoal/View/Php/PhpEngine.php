<?php

namespace Charcoal\View\Php;

// From 'charcoal-view'
use Charcoal\View\AbstractEngine;

/**
 * PHP view rendering engine
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
        $arrayContext = json_decode(json_encode($context), true);
        // Prevents leaking global variable by forcing anonymous scope
        $render = function($templateString, array $context) {
            extract($context);
            return eval('?>'.$templateString);
        };

        ob_start();
        $render($templateString, $arrayContext);
        $output = ob_get_clean();

        return $output;
    }
}
