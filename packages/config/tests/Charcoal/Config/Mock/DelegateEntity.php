<?php

namespace Charcoal\Tests\Config\Mock;

use InvalidArgumentException;

// From 'charcoal-config'
use Charcoal\Config\DelegatesAwareInterface;
use Charcoal\Config\DelegatesAwareTrait;

/**
 * Mock object of {@see \Charcoal\Tests\Config\Mixin\DelegatesAwareTest}
 */
class DelegateEntity extends Entity implements DelegatesAwareInterface
{
    use DelegatesAwareTrait {
        DelegatesAwareTrait::hasInDelegates as public;
        DelegatesAwareTrait::getInDelegates as public;
    }

    /**
     * @return \Charcoal\Config\EntityInterface[]
     */
    public function delegates(): array
    {
        return $this->delegates;
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

        $key = $this->camelize($key);

        /** @internal Edge Case: "_" → "" */
        if ($key === '') {
            return false;
        }

        if (is_callable([ $this, $key ])) {
            $value = $this->{$key}();
        } else {
            if (!isset($this->{$key})) {
                return $this->hasInDelegates($key);
            }
            $value = $this->{$key};
        }

        return ($value !== null);
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

        $key = $this->camelize($key);

        /** @internal Edge Case: "_" → "" */
        if ($key === '') {
            return null;
        }

        if (is_callable([ $this, $key ])) {
            return $this->{$key}();
        } else {
            if (isset($this->{$key})) {
                return $this->{$key};
            } else {
                return $this->getInDelegates($key);
            }
        }
    }
}
