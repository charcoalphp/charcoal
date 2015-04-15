<?php

namespace Charcoal\Config;

use \Charcoal\Config\ConfigInterface as ConfigInterface;

/**
* Configurable Interface defines object that can be configured with a Config object.
*
* This interface can also be implemented with `ConfigurableTrait`
*/
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
