<?php

namespace Charcoal\View\Engine;

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
     * @param string $templateIdent The template identifier to load and render.
     * @param mixed  $context       The rendering context.
     * @throws InvalidArgumentException If the template ident is not a string.
     * @return string The rendered template string.
     */
    public function render($templateIdent, $context)
    {
        if (!is_string($templateIdent)) {
            throw new InvalidArgumentException(
                'Render method called with invalid templateIdent parameter (not a string).'
            );
        }

        // Prevents leaking global variable by forcing anonymous scope
        $render = function($templateIdent, $context) {
            extract($context);
            include $templateIdent;
        };

        ob_start();
        $render($templateIdent, $context);
        $output = ob_get_clean();

        return $output;
    }

    /**
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    public function renderTemplate($templateString, $context)
    {
        return $templateString;
    }
}
