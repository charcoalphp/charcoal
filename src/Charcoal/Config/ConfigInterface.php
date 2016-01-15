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
    public function setDelegates(array $delegates);

    /**
     * @param ConfigInterface $delegate A config object to add as delegate.
     * @return ConfigInterface Chainable
     */
    public function addDelegate(ConfigInterface $delegate);

    /**
     * @param ConfigInterface $delegate A config object to prepend as delegate.
     * @return ConfigInterface Chainable
     */
    public function prependDelegate(ConfigInterface $delegate);

    /**
     * @param string $separator The separator character.
     * @return ConfigInterface Chainable
     */
    public function setSeparator($separator);

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
    public function addFile($filename);

    /**
     * @param string $filename The file to load.
     * @return mixed The file content.
     */
    public function loadFile($filename);
}
