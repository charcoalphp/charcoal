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
     * Get the configuration's available keys.
     *
     * @return array
     */
    public function keys();

    /**
     * @param array|Traversable $data The map of [$key=>$item] items to set.
     * @return ConfigInterface Chainable
     */
    public function merge($data);

    /**
     * Get the configuration data, as an associative array map.
     *
     * @return array
     */
    public function data();

    /**
     * The default data, called from object's constructor.
     *
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
     * @param string $filename The file to load and add.
     * @return ConfigInterface Chainable
     */
    public function add_file($filename);

    /**
     * @param string $filename The file to load.
     * @return mixed The file content.
     */
    public function load_file($filename);
}
