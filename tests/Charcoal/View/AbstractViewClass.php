<?php

namespace Charcoal\Tests\View;

use \Charcoal\View\AbstractView as AbstractView;

/**
* Concrete implementation of AbstractView for Unit Tests.
*/
class AbstractViewClass extends AbstractView
{
    public function load_template($template)
    {
        unset($template);
        return '';
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
}
