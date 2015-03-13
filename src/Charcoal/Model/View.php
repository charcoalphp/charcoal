<?php
/**
*
*/

namespace Charcoal\Model;

use \Charcoal\Model\ViewController as ViewController;
use \Charcoal\Loader\ViewLoader as ViewLoader;

/**
*
*/
class View
{
	const ENGINE_MUSTACHE = 'mustache';
	const ENGINE_PHP_MUSTACHE = 'php_mustache';
	
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

	private $template_ident;
	private $template_type;

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

	public function load_template($template_ident)
	{
		$template_loader = new ViewLoader();
		$template = $template_loader->load($template_ident);
		$this->set_template($template);

		return $template;
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
}