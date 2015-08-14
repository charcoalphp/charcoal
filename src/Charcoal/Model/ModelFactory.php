<?php

namespace Charcoal\Model;

// Dependencies from `PHP`
use \Exception as Exception;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\IdentFactory as IdentFactory;

// Local namespace dependencies
use \Charcoal\Model\ModelInterface as ModelInterface;

/**
*
*/
class ModelFactory extends IdentFactory
{
    /**
    * @param array $data
    */
    protected function __construct()
    {
        $this->set_base_class('\Charcoal\Model\ModelInterface');
    }
}
