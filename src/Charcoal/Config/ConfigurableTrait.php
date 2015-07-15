<?php

namespace Charcoal\Config;

/**
* An implementation, as Trait, of the `ConfigurableInterface`.
*
* This Trait contains one additional abstract (protected) function: `create_config()`
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
        if (is_array($config)) {
            $this->_config = $this->create_config($config);
        } elseif (($config instanceof ConfigInterface)) {
            $this->_config = $config;
        } else {
            throw new \InvalidArgumentException('Config must be an array or a ConfigInterface object.');
        }
        return $this;
    }

    /**
    * @return ConfigInterface
    */
    public function config()
    {
        if ($this->_config === null) {
            $this->_config = $this->create_config();
        }
        return $this->_config;
    }

    /**
    * @param array|null $data
    * @return ConfigInterface
    */
    abstract protected function create_config($data = null);
}
