<?php

namespace Charcoal\Tests\Config;

use \Charcoal\Config\ConfigurableInterface as ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait as ConfigurableTrait;
use \Charcoal\Config\GenericConfig as GenericConfig;

/**
* Concrete implementation of AbstractConfig for Unit Tests.
*/
class ConfigurableClass implements ConfigurableInterface
{
    use ConfigurableTrait;

    /**
    * @param array $data Optional
    * @return AbstractConfigClass
    */
    protected function create_config(array $data = null)
    {

        $config = new GenericConfig();
        if (is_array($data)) {
            $config->set_data($data);
        }
        return $config;
    }
}
