<?php

namespace Charcoal\View\Mustache;

// 3rd-party libraries (`mustache/mustache`) dependencies
use \Mustache_Engine;
use \Mustache_LambdaHelper;
use \Mustache_Loader_CascadingLoader;
use \Mustache_Loader_StringLoader;

// Intra-module (`charcoal-view`) depentencies
use \Charcoal\View\EngineInterface;

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
    * @var mixed $cache
    */
    private $cache;

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

            // 'helpers' => [
            //     '_t' => function($str) {
            //         /** @todo Translate */
            //         return $this->_t($str);
            //     },
            //     'add_js' => function($js, Mustache_LambdaHelper $helper) {
            //         $js = $helper->render($js);
            //         return $this->add_js($js);

            //     },
            //     'js' => function() {
            //         return $this->js();
            //     },
            //     'add_js_requirement' => function($js_requirement) {
            //         return $this->add_js_requirement($js_requirement);
            //     },
            //     'js_requirements' => function() {
            //         return $this->js_requirements();
            //     },
            //     'add_css' => function($css, Mustache_LambdaHelper $helper) {
            //         $css = $helper->render($css);
            //         return $this->add_css($css);
            //     },
            //     'css' => function() {
            //         return $this->css();
            //     }
            // ]
        ]);
        return $mustache;
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
