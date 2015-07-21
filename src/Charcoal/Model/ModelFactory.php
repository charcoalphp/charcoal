<?php

namespace Charcoal\Model;

// Dependencies from `PHP`
use \Exception as Exception;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\AbstractFactory as AbstractFactory;

// Local namespace dependencies
use \Charcoal\Model\ModelInterface as ModelInterface;

/**
*
*/
class ModelFactory extends AbstractFactory
{
    /**
    * @param array $data
    */
    protected function __construct(array $data = null)
    {
        $this->set_factory_mode(AbstractFactory::MODE_IDENT);
        $this->set_base_class('\Charcoal\Model\ModelInterface');
        if ($data !== null) {
            $this->set_data($data);
        }
    }
}
