<?php

namespace Charcoal\Config;

// Local namespace dependencies
use Charcoal\Config\ConfigInterface;

/**
 * Configurable Interface defines object that can be configured with a Config object.
 *
 * This interface can be fully implemented with its accompanying `ConfigurableTrait`.
 */
interface ConfigurableInterface
{
    /**
     * Set the object's configuration container.
     *
     * @param ConfigInterface|array $config The config object or data.
     * @return ConfigurableInterface Chainable
     */
    public function setConfig($config);

    /**
     * Retrieve the object's configuration container, or one of its entry.
     *
     * If the object has no existing config, create one.
     *
     * If a key is provided, return the configuration key value instead of the full object.
     *
     * @param string $key Optional. If provided, the config key value will be returned, instead of the full object.
     * @return ConfigInterface
     */
    public function config($key = null);
}
