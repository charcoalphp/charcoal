<?php

namespace Charcoal\View\Mustache;

// 3rd-party libraries (`mustache/mustache`) dependencies
use \Mustache_Engine;

// Intra-module (`charcoal-view`) depentencies
use \Charcoal\View\EngineInterface;
use \Charcoal\View\LoaderInterface;

// Local namespace dependencies
use \Charcoal\View\Mustache\MustacheLoader;

/**
*
*/
class MustacheEngine implements EngineInterface
{

    /**
    * @var MustacheLoader $loader
    */
    private $loader;

    /**
    * @var GenericHelper $helper
    */
    private $helper;

    /**
    * @var Mustache_Engine $mustache
    */
    private $mustache;

    /**
    * @var \Psr\Log\LoggerInterface $logger
    */
    private $logger;

    /**
    * @param array $data
    */
    public function __construct($data)
    {
        $this->logger = $data['logger'];
        if (isset($data['loader'])) {
            $this->loader = $data['loader'];
        }
    }

    /**
    * @return Mustache_Engine
    */
    public function mustache()
    {
        if ($this->mustache === null) {
            $this->mustache = $this->create_mustache();
        }
        return $this->mustache;
    }
    
    /**
    * @return Mustache_Engine
    */
    public function create_mustache()
    {
        $mustache = new Mustache_Engine([
            'cache' => 'mustache_cache',

            'loader' =>  $this->loader(),
            'partials_loader' => $this->loader(),

            //'logger' => $this->logger,

            'strict_callables' => true,

            'helpers' => $this->helper()
        ]);
        return $mustache;
    }

    /**
    * @param LoaderInterface $loader
    * @return MustacheEngine Chainable
    */
    public function set_loader(LoaderInterface $loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
    * @return MustacheLoader
    */
    public function loader()
    {
        if ($this->loader === null) {
            $this->loader = $this->create_loader();
        }
        return $this->loader;
    }

    /**
    * @return MustacheLoader
    */
    public function create_loader()
    {
        $loader = new MustacheLoader([
            'search_path'=>[]
        ]);
        return $loader;
    }

    /**
    * @param mixed $helper
    * @return MustacheEngine Chainable
    */
    public function set_helper($helper)
    {
        $this->helper = $helper;
        return $this;
    }

    /**
    * @return MustacheLoader
    */
    public function helper()
    {
        if ($this->helper === null) {
            $this->helper = $this->create_helper();
        }
        return $this->helper;
    }

    /**
    * @return MustacheLoader
    */
    public function create_helper()
    {
        $helper = new GenericHelper();
        return $helper;
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
