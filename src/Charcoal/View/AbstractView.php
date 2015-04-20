<?php

namespace Charcoal\View;

use \Mustache_Engine as Mustache_Engine;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\View\MustachePartialsLoader as MustachePartialsLoader;
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
    const ENGINE_PHP_MUSTACHE = 'php_mustache';
    const ENGINE_PHP = 'php';
    
    private $_engine = self::ENGINE_PHP_MUSTACHE;

    private $_template;
    private $_context;

    protected $_controller;

    /**
    * @param string $template
    * @param mixed  $controller
    */
    public function __construct($data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
    }

    /**
    *
    */
    public function __toString()
    {
        echo $this->render();
    }

    /**
    * @param array $data
    * @throws \InvalidArgumentException If data is not an array
    * @return AbstractView Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Data needs to be an array');
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
    * @param string
    * @throws \InvalidArgumentException if the provided argument is not a string
    * @return View (chainable)
    */
    public function set_template($template)
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException('Template must be a string');
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

    public function set_context($context)
    {
        $this->_context = $context;
        return $this;
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
    * @param
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
    *
    *
    * @param string $template
    * @param mixed  $controller
    *
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

        $mustache = new Mustache_Engine([
            'cache' => 'mustache_cache',
            
            //'loader' =>  null,
            'partials_loader' => new MustacheTemplateLoader(),

            'logger' => Charcoal::logger(),

            'strict_callables' => true
        ]);
        $controller = $this->controller();
        //var_dump($controller->length());
        return $mustache->render($this->template(), $controller);
    }

    /**
    * @param string $template_ident
    * @param mixed $context
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
        $this->load_template($ident);
        $this->load_context($ident);

        return $this;
    }

    /**
    * @param string @ident
    * @return string
    */
    protected function _ident_to_classname($ident)
    {
        $class = str_replace('/', '\\', $ident);
        $expl = explode('\\', $class);
        array_walk(
            $expl, function(&$i) {
                $i = ucfirst($i);
            }
        );
        $class = '\\'.implode('\\', $expl);
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
