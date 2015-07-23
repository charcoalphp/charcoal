<?php

namespace Charcoal\View;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// 3rd-party libraries dependencies
use \Mustache_Engine as Mustache_Engine;
use \Mustache_LambdaHelper as Mustache_LambdaHelper;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Charcoal as Charcoal;

// Local namespace dependencies
use \Charcoal\View\MustachePartialsLoader as MustachePartialsLoader;
use \Charcoal\View\ViewableInterface as ViewableInterface;
use \Charcoal\View\ViewInterface as ViewInterface;
use \Charcoal\View\ViewControllerInterface as ViewControllerInterface;

/**
* An abstract class that fulfills the full ViewInterface.
*
* There are 2 remaining abstract methods:
* - `load_template()`
* - `load_context()`
*/
abstract class AbstractView implements ViewInterface
{
    const ENGINE_MUSTACHE = 'mustache';
    const ENGINE_PHP_MUSTACHE = 'php-mustache';
    const ENGINE_PHP = 'php';

    const DEFAULT_ENGINE = self::ENGINE_MUSTACHE;

    /**
    * @var string $_engine
    */
    private $_engine = self::DEFAULT_ENGINE;

    /**
    * @var string $_template
    */
    private $_template;

    /**
    * @var mixed $_context;
    */
    private $_context;

    /**
    * @var ViewControllerInterface $_controlle
    */
    protected $_controller;

    /**
    * @var array $_js_requirements
    */
    static private $_js_requirements = [];
    /**
    * @var string $_js
    */
    static private $_js = '';
    /**
    * @var array $_css_requirements
    */
    static private $_css_requirements = [];
    /**
    * @var string $_css;
    */
    static private $_css = '';

    /**
    * @param array $data Optional
    */
    public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
    }

    /**
    * @return string
    */
    public function __toString()
    {
        return $this->render();
    }

    /**
    * Set the data / properties of this view.
    *
    * Base data is:
    * - `engine` (_string_) - The engine type to use. If unset, the default will be used.
    * - `template` (_mixed_) - The actual template to render.
    * - `context` (_mixed_) - The context (view data) to render the template with.
    *
    * @param array $data
    * @return AbstractView Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['engine']) && $data['engine'] !== null) {
            $this->set_engine($data['engine']);
        }
        if (isset($data['template']) && $data['template'] !== null) {
            $this->set_template($data['template']);
        }
        if (isset($data['context']) && $data['context'] !== null) {
            $this->set_context($data['context']);
        }

        return $this;
    }

    /**
    * Set the engine type
    *
    * @param string $engine
    * @throws InvalidArgumentException
    * @return AbstractView Chainable
    */
    public function set_engine($engine)
    {
        if (!is_string($engine)) {
            throw new InvalidArgumentException('Engine must be a string.');
        }
        $this->_engine = $engine;
        return $this;
    }

    /**
    * @return string
    */
    public function engine()
    {
        return $this->_engine;
    }

    /**
    * @param string $template
    * @throws InvalidArgumentException if the provided argument is not a string
    * @return View (chainable)
    */
    public function set_template($template)
    {
        if (!is_string($template)) {
            throw new InvalidArgumentException('Template must be a string.');
        }

        $this->_template = $template;
        return $this;
    }

    /**
    * @return string
    */
    public function template()
    {
        if ($this->_template === null) {
            return '';
        }

        return $this->_template;
    }

    /**
    * @param string $template_ident
    * @return string The template content
    */
    abstract public function load_template($template_ident);

    /**
    * @param mixed $context
    * @return AbstractView Chainable
    */
    public function set_context($context)
    {
        $this->_context = $context;
        if ($context instanceof ViewableInterface) {
            $this->set_engine($context->template_engine());
        }
        return $this;
    }

    /**
    * @param string $str
    * @return string
    */
    public function _t($str)
    {
        return $str;
    }

    /**
    * @return string
    */
    public function js_requirements()
    {
        $req = array_unique(self::$_js_requirements);
        $ret = '';
        foreach ($req as $r) {
            $ret .= $r;
        }
        self::$_js_requirements = [];
        return $ret;
    }

    /**
    * @param string $js_requirement
    * @return void
    */
    public function add_js_requirement($js_requirement)
    {
        self::$_js_requirements[] = $js_requirement;
    }

    /**
    * @return string
    */
    public function js()
    {
        $js = self::$_js;
        self::$_js = '';
        return $js;
    }

    /**
    * @param string $js
    * @return void
    */
    public function add_js($js)
    {
        self::$_js .= $js;
    }

    /**
    * @return array
    */
    public function css_requirements()
    {
        $req = array_unique(self::$_css_requirements);
        $ret = '';
        foreach ($req as $r) {
            $ret .= $r;
        }
        self::$_css_requirements = [];
        return $ret;
    }

    /**
    * @param string $css_requirement
    * @return void
    */
    public function add_css_requirement($css_requirement)
    {
        self::$_css_requirements[] = $css_requirement;
    }

    /**
    * @return string
    */
    public function css()
    {
        $css = self::$_css;
        self::$_css = '';
        return $css;
    }

    /**
    * @param string $css
    * @return void
    */
    public function add_css($css)
    {
        self::$_css .= $css;
    }

    /**
    * @return mixed
    */
    public function context()
    {
        return $this->_context;
    }

    /**
    * @param string $context_ident
    * @return mixed The context object / data
    */
    abstract public function load_context($context_ident);

    /**
    * @param ViewControllerInterface $controller
    * @return AbstractView Chainable
    */
    public function set_controller(ViewControllerInterface $controller)
    {
        $this->_controller = $controller;
        return $this;
    }

    /**
    * @return \Charcoal\View\Controller
    */
    public function controller()
    {
        if ($this->_controller === null) {
            $this->_controller = $this->create_controller();
        }
        return $this->_controller;
    }

    /**
    * @return ViewControllerInterface
    */
    abstract public function create_controller();

    /**
    * @param string $template
    * @param mixed  $context
    * @return string The rendered template
    */
    public function render($template = null, $context = null)
    {
        if ($template !== null) {
            $this->set_template($template);
        }
        if ($context !== null) {
            $this->set_context($context);
        }

        $engine = $this->engine();
        if ($engine == self::ENGINE_MUSTACHE || $engine == self::ENGINE_PHP_MUSTACHE) {
            $mustache = new Mustache_Engine(
                [
                    'cache' => 'mustache_cache',

                    // 'loader' =>  null,
                    'partials_loader' => new MustacheTemplateLoader(),

                    'logger' => Charcoal::logger(),

                    'strict_callables' => true,

                    'helpers' => [
                        '_t' => function($str) {
                            /** @todo Translate */
                            return $this->_t($str);
                        },
                        'add_js' => function($js, Mustache_LambdaHelper $helper) {
                            $js = $helper->render($js);
                            return $this->add_js($js);

                        },
                        'js' => function() {
                            return $this->js();
                        },
                        'add_js_requirement' => function($js_requirement) {
                            return $this->add_js_requirement($js_requirement);
                        },
                        'js_requirements' => function() {
                            return $this->js_requirements();
                        },
                        'add_css' => function($css, Mustache_LambdaHelper $helper) {
                            $css = $helper->render($css);
                            return $this->add_css($css);
                        },
                        'css' => function() {
                            return $this->css();
                        }
                    ]
                ]
            );
            $controller = $this->controller();
            return $mustache->render($this->template(), $controller);
        } else {
            return $this->template();
        }
    }

    /**
    * @param string $template_ident
    * @param mixed  $context
    * @return string The rendered templated
    */
    public function render_template($template_ident = '', $context = null)
    {
        // Load the View
        $template = $this->load_template($template_ident);
        return $this->render($template, $context);
    }

    /**
    * @param string $ident
    * @return AbstractView Chainable
    */
    public function from_ident($ident)
    {
        $this->load_context($ident);
        $this->load_template($ident);

        return $this;
    }

    /**
    * @param string $ident
    * @return string
    */
    protected function _ident_to_classname($ident)
    {
        // Change "foo-bar" to "fooBar"
        $expl = explode('-', $ident);
        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );
        $ident = implode('', $expl);

        // Change "/foo/bar" to "\Foo\Bar"
        $class = str_replace('/', '\\', $ident);
        $expl  = explode('\\', $class);
        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );

        $class = '\\'.trim(implode('\\', $expl), '\\');
        return $class;
    }

    /**
    * @param string $classname
    * @return string
    */
    protected function _classname_to_ident($classname)
    {
        $ident = str_replace('\\', '/', strtolower($classname));
        $ident = ltrim($ident, '/');
        return $ident;
    }
}
