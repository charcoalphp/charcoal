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

    protected function create_config($data = null)
    {
        include_once 'AbstractConfigClass.php';
        $config = new AbstractConfigClass();
        if ($data !== null) {
            $config->set_data($data);
        }
        return $config;
    }
}
