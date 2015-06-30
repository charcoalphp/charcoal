<?php

namespace Charcoal\Tests\Config;

use \Charcoal\Config\ConfigurableInterface as ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait as ConfigurableTrait;

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
        include_once 'AbstractConfigClass.php';
        $config = new AbstractConfigClass();
        if (is_array($data)) {
            $config->set_data($data);
        }
        return $config;
    }
}
