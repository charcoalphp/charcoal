<?php

namespace Charcoal\Model;

use \Charcoal\View\AbstractView as AbstractView;
use \Charcoal\Model\ModelViewController as ModelViewController;

class ModelView extends AbstractView
{
    /**
    * @return \Charcoal\View\ViewControllerBase
    */
    public function controller()
    {
        if($this->_controller === null) {
            $this->_controller = new ModelViewController($this->context());
        }
        return $this->_controller;
    }
}
