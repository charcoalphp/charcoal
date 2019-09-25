<?php

namespace Charcoal\Tests\Config\Mock;

use InvalidArgumentException;

// From 'charcoal-config'
use Charcoal\Config\SeparatorAwareInterface;
use Charcoal\Config\SeparatorAwareTrait;

/**
 * Mock object of {@see \Charcoal\Tests\Config\Mixin\SeparatorAwareTest}
 */
class TreeEntity extends Entity implements SeparatorAwareInterface
{
    use SeparatorAwareTrait {
        SeparatorAwareTrait::hasWithSeparator as public;
        SeparatorAwareTrait::getWithSeparator as public;
        SeparatorAwareTrait::setWithSeparator as public;
    }

    /**
     * Determines if this entity contains the specified key and if its value is not NULL.
     *
     * @param  string $key The data key to check.
     * @throws InvalidArgumentException If the $key is not a string or is a numeric value.
     * @return boolean TRUE if $key exists and has a value other than NULL, FALSE otherwise.
     */
    public function offsetExists($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys'
            );
        }

        if ($this->separator && strstr($key, $this->separator)) {
            return $this->hasWithSeparator($key);
        }

        $key = $this->camelize($key);

        /** @internal Edge Case: "_" → "" */
        if ($key === '') {
            return false;
        }

        $getter = 'get'.ucfirst($key);
        if (!isset($this->accessorsCache[$getter])) {
            $this->accessorsCache[$getter] = is_callable([ $this, $getter ]);
        }

        if ($this->accessorsCache[$getter]) {
            return ($this->{$getter}() !== null);
        }

        // -- START DEPRECATED
        if (!isset($this->accessorsCache[$key])) {
            $this->accessorsCache[$key] = is_callable([ $this, $key ]);
        }

        if ($this->accessorsCache[$key]) {
            return ($this->{$key}() !== null);
        }
        // -- END DEPRECATED

        if (isset($this->{$key})) {
            return true;
        }

        return false;
    }

    /**
     * Returns the value from the specified key on this entity.
     *
     * @param  string $key The data key to retrieve.
     * @throws InvalidArgumentException If the $key is not a string or is a numeric value.
     * @return mixed Value of the requested $key on success, NULL if the $key is not set.
     */
    public function offsetGet($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys'
            );
        }

        if ($this->separator && strstr($key, $this->separator)) {
            return $this->getWithSeparator($key);
        }

        $key = $this->camelize($key);

        /** @internal Edge Case: "_" → "" */
        if ($key === '') {
            return null;
        }

        $getter = 'get'.ucfirst($key);
        if (!isset($this->accessorsCache[$getter])) {
            $this->accessorsCache[$getter] = is_callable([ $this, $getter ]);
        }

        if ($this->accessorsCache[$getter]) {
            return $this->{$getter}();
        }

        // -- START DEPRECATED
        if (!isset($this->accessorsCache[$key])) {
            $this->accessorsCache[$key] = is_callable([ $this, $key ]);
        }

        if ($this->accessorsCache[$key]) {
            return $this->{$key}();
        }
        // -- END DEPRECATED

        if (isset($this->{$key})) {
            return $this->{$key};
        }

        return null;
    }

    /**
     * Assigns the value to the specified key on this entity.
     *
     * @param  string $key   The data key to assign $value to.
     * @param  mixed  $value The data value to assign to $key.
     * @throws InvalidArgumentException If the $key is not a string or is a numeric value.
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys'
            );
        }

        if ($this->separator && strstr($key, $this->separator)) {
            $this->setWithSeparator($key, $value);
            return;
        }

        $key = $this->camelize($key);

        /** @internal Edge Case: "_" → "" */
        if ($key === '') {
            return;
        }

        $setter = 'set'.ucfirst($key);
        if (!isset($this->accessorsCache[$setter])) {
            $this->accessorsCache[$setter] = is_callable([ $this, $setter ]);
        }

        if ($this->accessorsCache[$setter]) {
            $this->{$setter}($value);
        } else {
            $this->{$key} = $value;
        }

        $this->keysCache[$key] = true;
    }
}
