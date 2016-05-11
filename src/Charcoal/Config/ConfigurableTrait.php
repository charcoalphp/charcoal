<?php

namespace Charcoal\Config;

use \InvalidArgumentException;


// Local namespace depeendencies
use \Charcoal\Config\ConfigInterface
use \Charcoal\Config\GenericConfig;

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
    public function setConfig($config)
    {
        if (is_array($config)) {
            $this->config = $this->createConfig($config);
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
     * Retrieve the object's configuration container, or one of its entry.
     *
     * If the object has no existing config, create one.
     *
     * If a key is provided, return the configuration key value instead of the full object.
     *
     * @param string $key Optional. If provided, the config key value will be returned, instead of the full object.
     * @see    self::create_config()
     * @return ConfigInterface
     */
    public function config($key = null)
    {
        if ($this->config === null) {
            $this->config = $this->createConfig();
        }
        if ($key !== null) {
            return $this->config->get($key);
        } else {
            return $this->config;
        }
    }

    /**
     * Retrieve a new ConfigInterface instance for the object.
     *
     * @see    AbstractConfig::__construct()
     * @param  array|string|null $data Optional data to pass to the new ConfigInterface instance.
     * @return ConfigInterface
     */
    protected function createConfig($data = null)
    {
        return new GenericConfig($data);
    }
}
