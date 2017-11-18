<?php

namespace Charcoal\Config;

use Traversable;

/**
 * Config Interface
 */
interface ConfigInterface extends
    DelegatesAwareInterface,
    EntityInterface,
    SeparatorAwareInterface
{

    /**
     * @param array|\Traversable $data The map of [$key=>$item] items to set.
     * @return ConfigInterface Chainable
     */
    public function merge($data);

    /**
     * The default data, called from object's constructor.
     *
     * @return array
     */
    public function defaults();

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
