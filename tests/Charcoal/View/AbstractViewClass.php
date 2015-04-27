<?php

namespace Charcoal\Tests\View;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\View\AbstractView as AbstractView;
use \Charcoal\View\MustacheTemplateLoader as MustacheTemplateLoader;

/**
* Concrete implementation of AbstractView for Unit Tests.
*/
class AbstractViewClass extends AbstractView
{
    public function load_template($template_ident)
    {
        Charcoal::config()->add_template_path(__DIR__.'/templates');
        $loader = new MustacheTemplateLoader();
        return $loader->load($template_ident);
    }

    public function load_context($context)
    {
        unset($context);
        return [];
    }

    public function create_controller($data = null)
    {
        unset($data);
        return $this->context();
    }

    public function ident_to_classname($ident)
    {
        return $this->_ident_to_classname($ident);
    }

    public function classname_to_ident($classname)
    {
        return $this->_classname_to_ident($classname);
    }
}
