<?php

namespace Charcoal\Config;

/**
 * Describes an object that can read file contents.
 *
 * This interface can be fully implemented with its accompanying {@see FileAwareTrait}.
 */
interface FileAwareInterface
{
    /**
     * Loads a configuration file.
     *
     * @param  string $path A path to a supported file.
     * @return array An array on success.
     */
    public function loadFile($path);
}
