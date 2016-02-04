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
        $this->setLogger($data['logger']);

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
     * @param string $templateIdent
     * @param mixed  $context
     * @throws InvalidArgumentException
     * @return string
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
     * @param string $templateString
     * @param mixed  $context
     * @return string
     */
    public function renderTemplate($templateString, $context)
    {
        return $templateString;
    }
}
