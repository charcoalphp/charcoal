<?php

namespace Charcoal\Config;

use ArrayAccess;
use Exception;
use InvalidArgumentException;

/**
 *
 */
trait SeparatorAwareTrait
{
    /**
     * Delimiter for accessing nested options.
     *
     * Is empty by default (which disables the separator feature).
     *
     * @var string $separator
     */
    protected $separator = '';

    /**
     * @param string $separator A single-character to delimit nested options.
     * @throws InvalidArgumentException If $separator is invalid.
     * @return self
     */
    final public function setSeparator($separator)
    {
        if (!is_string($separator)) {
            throw new InvalidArgumentException(
                'Separator needs to be a string.'
            );
        }
        if (strlen($separator) > 1) {
            throw new InvalidArgumentException(
                'Separator needs to be only one-character, or empty.'
            );
        }
        $this->separator = $separator;
        return $this;
    }

    /**
     * @return string
     */
    final public function separator()
    {
        return $this->separator;
    }

    /**
     * @param string $key The key of the configuration item to look for.
     * @return mixed The value (or null)
     */
    final protected function getWithSeparator($key)
    {
        $structure = $this;
        $splitKeys = explode($this->separator, $key);
        foreach ($splitKeys as $k) {
            if (!isset($structure[$k])) {
                return null;
            }
            if (!is_array($structure[$k])) {
                return $structure[$k];
            }
            $structure = $structure[$k];
        }
        return $structure;
    }

    /**
     * @param string $key The key of the configuration item to look for.
     * @return boolean
     */
    final protected function hasWithSeparator($key)
    {
        $structure = $this;
        $splitKeys = explode($this->separator, $key);
        foreach ($splitKeys as $k) {
            if (!isset($structure[$k])) {
                return false;
            }
            if (!is_array($structure[$k])) {
                return true;
            }
            $structure = $structure[$k];
        }
        return true;
    }

    /**
     * @param string $key   The key to assign $value to.
     * @param mixed  $value Value to assign to $key.
     * @throws Exception If a value already exists and is scalar (can not be merged).
     * @return void
     */
    final protected function setWithSeparator($key, $value)
    {
        $splitKeys = array_reverse(explode($this->separator, $key));

        $structure = [];
        $prev = $splitKeys[0];
        $currentVal = $value;
        foreach ($splitKeys as $k) {
            $structure[$k] = $currentVal;
            $currentVal = $structure;
            unset($structure[$prev]);
            $prev = $k;
        }
        if (isset($this[$k]) && is_array($this[$k])) {
            $this[$k] = array_replace_recursive($this[$k], $structure[$k]);
        } else {
            $this[$k] = $structure[$k];
        }
    }
}
