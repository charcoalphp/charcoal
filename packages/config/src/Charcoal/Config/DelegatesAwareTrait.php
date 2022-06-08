<?php

namespace Charcoal\Config;

/**
 * Provides an object with the ability to perform lookups in other objects.
 *
 * A "delegate object" acts as a fallback when the current object does not have a requested value.
 *
 * This is a full implementation of {@see DelegatesAwareInterface}.
 */
trait DelegatesAwareTrait
{
    /**
     * Holds a list of all delegate objects.
     *
     * @var EntityInterface[]
     */
    private $delegates = [];

    /**
     * Assigns a collection of delegare objects.
     *
     * @param  EntityInterface[] $delegates One or more delegate objects to register.
     * @return self
     */
    final public function setDelegates(array $delegates)
    {
        $this->delegates = [];
        foreach ($delegates as $delegate) {
            $this->addDelegate($delegate);
        }
        return $this;
    }

    /**
     * Appends a delegare object onto the delegate stack.
     *
     * @param  EntityInterface $delegate A delegate object to register.
     * @return self
     */
    final public function addDelegate(EntityInterface $delegate)
    {
        $this->delegates[] = $delegate;
        return $this;
    }

    /**
     * Prepends a delegare object onto the delegate stack.
     *
     * @param  EntityInterface $delegate A delegate object to register.
     * @return self
     */
    final public function prependDelegate(EntityInterface $delegate)
    {
        array_unshift($this->delegates, $delegate);
        return $this;
    }

    /**
     * Determines if a delegate object contains the specified key and if its value is not NULL.
     *
     * Iterates over each object in the delegate stack and stops on
     * the first match containing the specified key.
     *
     * @param  string $key The data key to check.
     * @return boolean TRUE if $key exists and has a value other than NULL, FALSE otherwise.
     */
    final protected function hasInDelegates($key)
    {
        foreach ($this->delegates as $delegate) {
            if (isset($delegate[$key])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the value from the specified key found on the first delegate object.
     *
     * Iterates over each object in the delegate stack and stops on
     * the first match containing a value that is not NULL.
     *
     * @param  string $key The data key to retrieve.
     * @return mixed Value of the requested $key on success, NULL if the $key is not set.
     */
    final protected function getInDelegates($key)
    {
        foreach ($this->delegates as $delegate) {
            if (isset($delegate[$key])) {
                return $delegate[$key];
            }
        }
        return null;
    }
}
