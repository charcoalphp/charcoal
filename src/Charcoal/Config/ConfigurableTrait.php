<?php

namespace Charcoal\Config;

use \InvalidArgumentException;

/**
* An implementation, as Trait, of the `ConfigurableInterface`.
*
* This Trait contains one additional abstract (protected) function: `create_config()`
*/
trait ConfigurableTrait
{
    /**
     * @var ConfigInterface $config
     */
    private $config;

    /**
     * @param ConfigInterface|array $config
     * @throws InvalidArgumentException if config is not an array or Config object
     * @return ConfigurableInterface Chainable
     */
    public function set_config($config)
    {
        if (is_array($config)) {
            $this->config = $this->create_config($config);
        } elseif (($config instanceof ConfigInterface)) {
            $this->config = $config;
        } else {
            throw new InvalidArgumentException('Config must be an array or a ConfigInterface object.');
        }
        return $this;
    }

    /**
     * @return ConfigInterface
     */
    public function config()
    {
        if ($this->config === null) {
            $this->config = $this->create_config();
        }
        return $this->config;
    }

    /**
     * @param array|null $data
     * @return ConfigInterface
     */
    abstract protected function create_config($data = null);
}
