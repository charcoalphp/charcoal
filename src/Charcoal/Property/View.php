<?php

namespace Charcoal\Property;
use \Charcoal\Property\ViewController as ViewController;
use \Charcoal\Loader\ViewLoader as ViewLoader;

class View extends \Charcoal\Model\View
{
	
	public function render_template($template_ident='', ViewController $controller=null)
	{
		// Load the View
		$template = $this->load_template('properties/'.$template_ident);
		return $this->render($template, $controller);
	}
}