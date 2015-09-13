<?php
/**
*
*/

namespace Charcoal\Property;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\View\AbstractView;

// Local namespace dependencies
use \Charcoal\Property\PropertyViewController;

/**
*
*/
class PropertyView extends AbstractView
{
    /**
    * AbstractView > load_template()
    *
    * @param string $template_ident
    * @throws \InvalidArgumentException if the ident is not a string
    * @return string
    */
    public function load_template($template_ident)
    {
        return '';
    }

    /**
    * AbstractView > load_context()
    *
    * @param string $context_ident
    * @throws \InvalidArgumentException if the ident is not a string
    * @return mixed
    */
    public function load_context($context_ident)
    {
        return null;
    }

    /**
    * AbstractView > create_controller()
    *
    * @return ViewControllerInterface
    */
    public function create_controller()
    {
        $context = $this->context();
        $controller = new PropertyViewController();
        $controller->set_context($context);
        return $controller;
    }
}
