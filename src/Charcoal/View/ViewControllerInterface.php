<?php

namespace Charcoal\View;

/**
* View Controller Interface
*/
interface ViewControllerInterface
{
    /**
    * @param mixed $context
    * @return ViewControllerInterface Chainable
    */
    public function set_context($context);

    /**
    * @return mixed
    */
    public function context();
}
