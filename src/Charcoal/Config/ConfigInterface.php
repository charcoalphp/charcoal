<?php

namespace Charcoal\Config;

/**
 * Config Interface
 */
interface ConfigInterface
{
    public function set_delegates(array $delegates);

    public function add_delegate(ConfigInterface $delegate);

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
     * @param array $data The map of [$key=>$item] items to set.
     * @return ConfigInterface Chainable
     */
    public function set_data(array $data);

    /**
     * @return array
     */
    public function default_data();

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
