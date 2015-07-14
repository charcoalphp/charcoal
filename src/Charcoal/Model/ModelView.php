<?php

namespace Charcoal\Model;

use \Charcoal\View\AbstractView as AbstractView;
use \Charcoal\Model\ModelViewController as ModelViewController;

class ModelView extends AbstractView
{
    /**
    * @param string $template_ident
    * @throws \InvalidArgumentException if the ident is not a string
    * @return string
    * @todo Implement template loader via $template_ident
    */
    public function load_template($template_ident)
    {
        return '';
    }

    /**
    * @param string $context_ident
    * @throws \InvalidArgumentException if the ident is not a string
    * @return mixed
    * @todo Implement context loader via $template_ident
    */
    public function load_context($context_ident)
    {
        return null;
    }

    /**
    * @return ViewControllerInterface
    */
    public function create_controller()
    {
        $context = $this->context();
        $controller = new ModelViewController();
        $controller->set_context($context);
        return $controller;
    }
}
