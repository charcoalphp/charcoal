<?php

namespace Charcoal\Config;

use InvalidArgumentException;

/**
 * Provides an object with the ability to perform lookups into multi-dimensional arrays.
 *
 * This is a full implementation of {@see SeparatorAwareInterface}.
 */
trait SeparatorAwareTrait
{
    /**
     * Token for accessing nested data.
     *
     * Is empty by default (which disables the separator feature).
     *
     * @var string
     */
    protected $separator = '';

    /**
     * Sets the token for traversing a data-tree.
     *
     * @param  string $separator The single-character token to delimit nested data.
     *     If the token is an empty string, data-tree traversal is disabled.
     * @throws InvalidArgumentException If the $separator is invalid.
     * @return self
     */
    final public function setSeparator($separator)
    {
        if (!is_string($separator)) {
            throw new InvalidArgumentException(
                'Separator must be a string'
            );
        }

        if (strlen($separator) > 1) {
            throw new InvalidArgumentException(
                'Separator must be one-character, or empty'
            );
        }

        $this->separator = $separator;
        return $this;
    }

    /**
     * Gets the token for traversing a data-tree, if any.
     *
     * @return string
     */
    final public function separator()
    {
        return $this->separator;
    }

    /**
     * Determines if this store contains the key-path and if its value is not NULL.
     *
     * Traverses each node in the key-path until the endpoint is located.
     *
     * @param  string $key The key-path to check.
     * @return boolean TRUE if $key exists and has a value other than NULL, FALSE otherwise.
     */
    final protected function hasWithSeparator($key)
    {
        $structure = $this;
        $splitKeys = explode($this->separator, $key);
        foreach ($splitKeys as $key) {
            if (!isset($structure[$key])) {
                return false;
            }
            if (!is_array($structure[$key])) {
                return true;
            }
            $structure = $structure[$key];
        }
        return true;
    }

    /**
     * Returns the value from the key-path found on this object.
     *
     * Traverses each node in the key-path until the endpoint is located.
     *
     * @param  string $key The key-path to retrieve.
     * @return mixed Value of the requested $key on success, NULL if the $key is not set.
     */
    final protected function getWithSeparator($key)
    {
        $structure = $this;
        $splitKeys = explode($this->separator, $key);
        foreach ($splitKeys as $key) {
            if (!isset($structure[$key])) {
                return null;
            }
            if (!is_array($structure[$key])) {
                return $structure[$key];
            }
            $structure = $structure[$key];
        }
        return $structure;
    }

    /**
     * Assign a value to the key-path, replacing / merging existing data with the same endpoint.
     *
     * Traverses, in reverse, each node in the key-path from the endpoint.
     *
     * @param  string $key   The key-path to assign $value to.
     * @param  mixed  $value The data value to assign to $key.
     * @return void
     */
    final protected function setWithSeparator($key, $value)
    {
        $structure = $value;
        $splitKeys = array_reverse(explode($this->separator, $key));
        foreach ($splitKeys as $key) {
            $structure = [
                $key => $structure
            ];
        }

        if (isset($this[$key]) && is_array($this[$key])) {
            $this[$key] = array_replace_recursive(
                $this[$key],
                $structure[$key]
            );
        } else {
            $this[$key] = $structure[$key];
        }
    }
}
