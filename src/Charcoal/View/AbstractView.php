<?php

namespace Charcoal\View;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\View\TemplateLoader as TemplateLoader;
use \Charcoal\View\ViewInterface as ViewInterface;
use \Charcoal\View\ViewControllerInterface as ViewControllerInterface;

/**
* An abstract class that fulfills the full ViewInterface
*/
abstract class AbstractView implements ViewInterface
{
    const ENGINE_MUSTACHE = 'mustache';
    const ENGINE_PHP_MUSTACHE = 'php_mustache';
    
    private $_engine = self::ENGINE_PHP_MUSTACHE;
    private $_template;
    private $_ident;
    protected $_controller;
    private $_context;

    public function from_ident($ident)
    {
        //$template_loader = new TemplateLoader();
        //$template = $template_loader->load($ident);
        //$this->set_template($template);
        $this->load_template($ident);

        $class_name = $this->_ident_to_classname($ident);
        if(class_exists($class_name)) {
            $model = new $class_name();
        }
        else {
            $model = [];
        }

        $this->set_context($model);

        return $this;
    }

    /**
    * @param string $template
    * @param mixed  $controller
    */
    public function __construct($data=null)
    {
        if($data !== null) {
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
        if(!is_array($data)) {
            throw new \InvalidArgumentException('Data needs to be an array');
        }

        if(isset($data['template']) && $data['template'] !== null) {
            $this->set_template($data['template']);
        }
        if(isset($data['context']) && $data['context'] !== null) {
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
        if(!is_string($template)) {
            throw new \InvalidArgumentException('Template must be a string');
        }

        $this->_template = $template;
        return $this;
    }

    /**
    *
    */
    public function template()
    {
        if($this->_template === null) {
            return '';
        }

        return $this->_template;
    }

    /**
    * @param string $template_ident
    * @throws \InvalidArgumentException if template is not a string
    * @return string The template content
    */
    public function load_template($template_ident)
    {
        if(!is_string($template_ident)) {
            throw new \InvalidArgumentException('Template ident must be a string');
        }

        $template_loader = new TemplateLoader();
        $template = $template_loader->load($template_ident);
        $this->set_template($template);

        return $template;
    }

    public function set_context($context)
    {
        $this->_context = $context;
        return $this;
    }

    public function context()
    {
        return $this->_context;
    }

    /**
    *
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
        if($this->_controller === null) {
            if(is_array($this->_context)) {
                return $this->context();
            }
            else {
                return [];
            }
        }
        return $this->_controller;
    }

    /**
    *
    *
    * @param string $template
    * @param mixed  $controller
    *
    * @return string Rendered template
    */
    public function render($template=null, $context=null)
    {
        if($template !== null) {
            $this->set_template($template);
        }
        if($context !== null) {
            $this->set_context($context);
        }

        $mustache = new \Mustache_Engine([
            'cache' => 'mustache_cache',
            
            //'loader' =>  null,
            //'partials_loader' => null,

            'logger' => Charcoal::logger(),

            'strict_callables' => true
        ]);
        $controller = $this->controller();
        //var_dump($controller->length());
        return $mustache->render($this->template(), $controller);
    }

    public function render_template($template_ident='', $context=null)
    {
        // Load the View
        $template = $this->load_template($template_ident);
        return $this->render($template, $context);
    }

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

    protected function _classname_to_ident($classname)
    {
        $ident = str_replace('\\', '/', strtolower($classname));
        $ident = ltrim($ident, '/');
        return $ident;
    }

}
