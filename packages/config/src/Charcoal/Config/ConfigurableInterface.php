<?php

namespace Charcoal\Config;

/**
 * Describes an object that can be configured with an instance of {@see ConfigInterface}.
 *
 * This interface can be fully implemented with its accompanying {@see ConfigurableTrait}.
 */
interface ConfigurableInterface
{
    /**
     * Sets the object's configuration container.
     *
     * @param  mixed $config The Config object or dataset.
     * @return ConfigurableInterface Chainable
     */
    public function setConfig($config);

    /**
     * Gets the object's configuration container or a specific key from the container.
     *
     * @param  string|null $key If provided, the data key to retrieve.
     * @return mixed If $key is NULL, the Config object is returned.
     *     If $key is given, its value on the Config object is returned.
     */
    public function config($key = null);
}
