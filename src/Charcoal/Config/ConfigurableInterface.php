<?php

namespace Charcoal\Config;

use \Charcoal\Config\ConfigInterface as ConfigInterface;

interface ConfigurableInterface
{
    /**
    * @param ConfigInterface|array $config
    * @return ConfigurableInterface Chainable
    */
    public function set_config($config);

    /**
    * @return ConfigInterface
    */
    public function config();
}
