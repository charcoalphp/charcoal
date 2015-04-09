<?php

namespace Charcoal\Config;

/**
* An implementation, as Trait, of the `ConfigurableInterface`
*/
trait ConfigurableTrait
{
    /**
    * @var ConfigInterface $_config
    */
    private $_config;

    /**
    * @param ConfigInterface|array $config
    * @throws \InvalidArgumentException if config is not an array or Config object
    * @return ConfigurableInterface Chainable
    */
    public function set_config($config)
    {
        if(is_array($config)) {
            $this->_config = $this->_config_from_array();
        }
        else if(($config instanceof ConfigInterface)) {
            $this->_config = $config;
        }
        else {
            throw new \InvalidArgumentException('Invalid config Argument');
        }
        $this->_config = $config;
        return $this;
    }

    /**
    * @return ConfigInterface
    */
    public function config()
    {
        return $this->_config;
    }

    abstract protected function _config_from_array($config);
}
