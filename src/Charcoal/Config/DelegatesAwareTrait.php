<?php

namespace Charcoal\Config;

/**
 *
 */
trait DelegatesAwareTrait
{
    /**
     * Delegates act as fallbacks when the current object
     * doesn't have a requested option.
     *
     * @var EntityInterface[]
     */
    private $delegates = [];

    /**
     * @param EntityInterface[] $delegates The array of delegates (config) to set.
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
     * @param EntityInterface $delegate A delegate (config) instance.
     * @return self
     */
    final public function addDelegate(EntityInterface $delegate)
    {
        $this->delegates[] = $delegate;
        return $this;
    }

    /**
     * @param EntityInterface $delegate A delegate (config) instance.
     * @return self
     */
    final public function prependDelegate(EntityInterface $delegate)
    {
        array_unshift($this->delegates, $delegate);
        return $this;
    }

    /**
     * @param string $key The key of the configuration item to check.
     * @return boolean
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
     * @param string $key The key of the configuration item to fetch.
     * @return mixed The item, if found, or null.
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
