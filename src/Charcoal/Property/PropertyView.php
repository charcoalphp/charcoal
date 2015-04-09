<?php
/**
*
*/

namespace Charcoal\Property;

use \Charcoal\View\AbstractView as AbstractView;
use \Charcoal\Property\PropertyViewController as PropertyViewController;

/**
*
*/
class PropertyView extends AbstractView
{
    /**
    * @return PropertyViewController
    */
    public function controller()
    {
        if($this->_controller === null) {
            $this->_controller = new PropertyViewController($this->context());
        }
        return $this->_controller;
    }
}
