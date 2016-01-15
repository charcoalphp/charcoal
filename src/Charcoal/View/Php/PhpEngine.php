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
     * @param array $data Dependencies
     */
    public function __construct($data)
    {
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }

        if (isset($data['loader'])) {
            $this->setLoader($data['loader']);
        }
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'php';
    }

    /**
     * @param string $template
     * @param mixed  $context
     * @throws InvalidArgumentException
     * @return string
     */
    public function render($template, $context)
    {
        if (!is_string($template)) {
            throw new InvalidArgumentException(
                'Render method called with invalid template parameter (not a string).'
            );
        }

        // Prevents leaking global variable by forcing anonymous scope
        $render = function($template, $context) {
            extract($context);
            include $template;
        };

        ob_start();
        $render($template, $context);
        $output = ob_get_clean();

        return $output;
    }
}
