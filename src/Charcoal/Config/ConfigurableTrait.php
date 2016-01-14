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
     * Set the object's configuration container.
     *
     * @param  ConfigInterface|array $config The datas to set.
     * @throws InvalidArgumentException If the parameter is invalid.
     * @return ConfigurableInterface Chainable
     */
    public function set_config($config)
    {
        if (is_array($config)) {
            $this->config = $this->create_config($config);
        } elseif ($config instanceof ConfigInterface) {
            $this->config = $config;
        } else {
            throw new InvalidArgumentException(
                'Configuration must be an array or a ConfigInterface object.'
            );
        }
        return $this;
    }

    /**
     * Retrieve the object's configuration container.
     *
     * If the object has no existing config, create one.
     *
     * @see    self::create_config()
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
     * Retrieve a new ConfigInterface instance for the object.
     *
     * @see    AbstractConfig::__construct()
     * @param  array|string|null $data Optional data to pass to the new ConfigInterface instance.
     * @return ConfigInterface
     */
    abstract protected function create_config($data = null);
}
