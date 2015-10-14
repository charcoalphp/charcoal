<?php

namespace Charcoal\View\Engine;

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
        $this->set_logger($data['logger']);
        if (isset($data['loader'])) {
            $this->set_loader($data['loader']);
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
    * @param mixed $context
    * @return string
    */
    public function render($template, $context)
    {
        return $this->mustache()->render($template, $context);
    }
}
