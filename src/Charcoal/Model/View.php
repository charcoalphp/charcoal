<?php
/**
*
*/

namespace Charcoal\Model;

use \Charcoal\Model\ViewController as ViewController;

/**
*
*/
class View
{
	const ENGINE_MUSTACHE = 'mustache';
	
	/**
	* Unused, for now (always mustache)
	* @var string $engine;
	*/
	public $engine = self::ENGINE_MUSTACHE;

	/**
	* The template (view), as a string
	* @var string $template
	*/
	private $template;
	/**
	* 
	* @var mixed $controller
	*/
	private $controller;

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

		$this->template = $template;
		return $this;
	}

	/**
	*
	*/
	public function template()
	{
		if($this->template === null) {
			return '';
		}

		return $this->template;
	}

	/**
	*
	*/
	public function set_controller(ViewController $controller)
	{

		$this->controller = $controller;
		return $this;
	}

	/**
	* @return \Charcoal\View\Controller 
	*/
	public function controller()
	{
		if($this->controller === null) {
			return [];
		}
		//var_dump($this->controller);
		return $this->controller;
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

		$m = new \Mustache_Engine([
			'logger' => new \Mustache_Logger_StreamLogger('php://stdout')
		]);
		$controller = $this->controller();
		//var_dump($controller->length());
		return $m->render($this->template(), $controller);
	}
}