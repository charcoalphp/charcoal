<?php

namespace Charcoal\Tests\View;

use \Charcoal\View\ViewableInterface as ViewableInterface;
use \Charcoal\View\ViewableTrait as ViewableTrait;

/**
* Concrete implementation of AbstractView for Unit Tests.
*/
class ViewableClass implements ViewableInterface
{
    use ViewableTrait;

    public $foo = 'bar';

    public function create_view($data = null)
    {
        include_once('AbstractViewClass.php');
        $view = new AbstractViewClass();
        if ($data !== null) {
            $view->set_data($data);
        }
        return $view;
    }
}
