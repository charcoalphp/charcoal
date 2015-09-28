<?php

namespace Charcoal\Model;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\IdentFactory as IdentFactory;

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
