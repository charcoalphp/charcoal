<?php

namespace Charcoal\View\Engine;

// Intra-module (`charcoal-view`) depentencies
use \Charcoal\View\EngineInterface;

/**
*
*/
class PhpEngine implements EngineInterface
{
    /**
    * @var PhpLoader $loader
    */
    private $loader;

    /**
    * @var \Psr\Log\LoggerInterface $logger
    */
    private $logger;

    public function __construct($data)
    {
        $this->logger = $data['logger'];
        $this->cache = $data['cache'];
        $this->loader = $data['loader'];
    }

    /**
    * @return string
    */
    public function type()
    {
        return 'php';
    }

    /**
    * @param string $template_ident
    * @return string
    */
    public function load_template($template_ident)
    {
        return $this->loader()->load($template_ident);
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
