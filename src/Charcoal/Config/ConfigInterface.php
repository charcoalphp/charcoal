<?php

namespace Charcoal\Config;

// Local namespace dependencies
use \Charcoal\Config\ConfigInterface;

/**
 * Config Interface
 */
interface ConfigInterface
{
    /**
     * @param ConfigInterface[] $delegates The list of delegates to add.
     * @return ConfigInterface Chainable.
     */
    public function set_delegates(array $delegates);

    /**
     * @param ConfigInterface $delegate A config object to add as delegate.
     * @return ConfigInterface Chainable
     */
    public function add_delegate(ConfigInterface $delegate);

    /**
     * @param ConfigInterface $delegate A config object to prepend as delegate.
     * @return ConfigInterface Chainable
     */
    public function prepend_delegate(ConfigInterface $delegate);

    /**
     * @param string $separator The separator character.
     * @return ConfigInterface Chainable
     */
    public function set_separator($separator);

    /**
     * @return string
     */
    public function separator();

    /**
     * @param array|Traversable $data The map of [$key=>$item] items to set.
     * @return ConfigInterface Chainable
     */
    public function merge($data);

    /**
     * @return array
     */
    public function defaults();

    /**
     * @param string $key The key of the configuration item to fetch.
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key The key of the configuration item to set.
     * @param mixed $val
     * @return ConfigInterface Chainable
     */
    public function set($key, $val);

    /**
     * @param string $key The key of the configuration item to check.
     * @return boolean
     */
    public function has($key);

    /**
     * @param string $path The file to load and add.
     * @return AbstractConfig (Chainable)
     */
    public function add_file($path);
}
