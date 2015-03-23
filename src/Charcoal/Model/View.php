<?php
/**
*
*/

namespace Charcoal\Model;

use \Charcoal\Model\ViewController as ViewController;
use \Charcoal\Loader\TemplateLoader as TemplateLoader;

/**
*
*/
class View
{
	const ENGINE_MUSTACHE = 'mustache';
	const ENGINE_PHP_MUSTACHE = 'php_mustache';
	
	private $_engine = self::ENGINE_PHP_MUSTACHE;
	private $_template;
	private $_ident;
	private $_controller;





	public function from_ident($ident)
	{
		$template_loader = new TemplateLoader();
		$template = $template_loader->load($ident);
		$this->set_template($template);

		$class_name = $this->_ident_to_classname($ident);
		if(class_exists($class_name)) {
			$model = new $class_name();
		}
		else {
			$model = new Model();
		}

		$controller = new ViewController($model);
		$this->set_controller($controller);

		return $this;
	}

	/**
	* @param string $template
	* @param mixed $controller
	*/
	public function __construct($template=null, ViewController $controller=null)
	{
		if($template !== null) {
			$this->set_template($template);
		}
		if($controller !== null) {
			$this->set_controller($controller);
		}
	}

	/**
	* @param string $template
	* @param mixed $controller
	*
	* @return string
	*/
	public function __invoke($template=null, ViewController $controller=null)
	{
		return $this->render($template, $controller);
	}

	/**
	*
	*/
	public function __toString()
	{
		echo $this->render();
	}

	/**
	* @param string
	*
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

	public function load_template($template_ident)
	{
		$template_loader = new TemplateLoader();
		$template = $template_loader->load($template_ident);
		$this->set_template($template);

		return $template;
	}

	/**
	*
	*/
	public function set_controller(ViewController $controller)
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
			return [];
		}
		return $this->_controller;
	}

	/**
	*
	*
	* @param string $template
	* @param mixed $controller
	*
	* @return string Rendered template
	*/
	public function render($template=null, ViewController $controller=null)
	{
		if($template !== null) {
			$this->set_template($template);
		}
		if($controller !== null) {
			$this->set_controller($controller);
		}

		$mustache = new \Mustache_Engine([
			'logger' => new \Mustache_Logger_StreamLogger('php://stdout')
		]);
		$controller = $this->controller();
		//var_dump($controller->length());
		return $mustache->render($this->template(), $controller);
	}

	public function render_template($template_ident='', ViewController $controller=null)
	{
		// Load the View
		$template = $this->load_template($template_ident);
		return $this->render($template, $controller);
	}

	private function _ident_to_classname($ident)
	{
		$class = str_replace(['/', '.'], '\\', $ident);
		$expl = explode('\\', $class);
		array_splice($expl, (count($expl)-1), 0, ['Template']);
		array_walk($expl, function(&$i) { 
			$i = ucfirst($i); 
		});
		$class = '\\'.implode('\\', $expl);
		return $class;
	}

}