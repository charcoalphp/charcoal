<?php

namespace Charcoal\Config;

use InvalidArgumentException;

/**
 * Provides an object with a {@see ConfigInterface configuration container}.
 *
 * This is a full implementation of {@see ConfigurableInterface}.
 */
trait ConfigurableTrait
{
    /**
     * The config object.
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     * Sets the object's configuration container.
     *
     * @param  mixed $config The Config object, datamap, or filepath.
     * @throws InvalidArgumentException If the parameter is invalid.
     * @return self Chainable
     */
    public function setConfig($config)
    {
        if (is_string($config)) {
            // Treat the parameter as a filepath
            $this->config = $this->createConfig($config);
        } elseif (is_array($config)) {
            $this->config = $this->createConfig($config);
        } elseif ($config instanceof ConfigInterface) {
            $this->config = $config;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Configset must be an associative array, a file path, or an instance of %s',
                ConfigInterface::class
            ));
        }

        return $this;
    }

    /**
     * Gets the object's configuration container or a specific key from the container.
     *
     * @param  string|null $key     If provided, the data key to retrieve.
     * @param  mixed       $default The fallback value to return if $key does not exist.
     * @return mixed If $key is NULL, the Config object is returned.
     *     If $key is given, its value on the Config object is returned.
     *     If the value of $key is NULL, the value of $default is returned.
     */
    public function config($key = null, $default = null)
    {
        if ($this->config === null) {
            $this->config = $this->createConfig();
        }

        if ($key !== null) {
            if ($this->config->has($key)) {
                return $this->config->get($key);
            } elseif (!is_string($default) && is_callable($default)) {
                return $default();
            } else {
                return $default;
            }
        }

        return $this->config;
    }

    /**
     * Create a new ConfigInterface instance for the object.
     *
     * @see    AbstractConfig
     * @param  mixed $data Initial data. Either a filepath, a datamap, or a Config object.
     * @return ConfigInterface A new Config object.
     */
    protected function createConfig($data = null)
    {
        return new GenericConfig($data);
    }
}
