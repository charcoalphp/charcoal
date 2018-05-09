<?php

namespace Charcoal\Config;

/**
 * Describes a configuration container / registry.
 */
interface ConfigInterface extends
    DelegatesAwareInterface,
    EntityInterface,
    FileAwareInterface,
    SeparatorAwareInterface
{
    /**
     * Gets the default data.
     *
     * Pre-populates new configsets.
     *
     * @return array Key-value array of data
     */
    public function defaults();

    /**
     * Adds new data, replacing / merging existing data with the same key.
     *
     * @param  array|\Traversable $data Key-value array of data to merge.
     * @return ConfigInterface Chainable
     */
    public function merge($data);

    /**
     * Add a configuration file to the configset.
     *
     * @param  string $path The file to load and add.
     * @return ConfigInterface Chainable
     */
    public function addFile($path);
}
