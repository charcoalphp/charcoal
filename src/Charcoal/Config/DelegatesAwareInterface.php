<?php

namespace Charcoal\Config;

/**
 *
 */
interface DelegatesAwareInterface
{
    /**
     * @param EntityInterface[] $delegates The list of delegates to add.
     * @return EntityInterface Chainable.
     */
    public function setDelegates(array $delegates);

    /**
     * @param EntityInterface $delegate A config object to add as delegate.
     * @return EntityInterface Chainable
     */
    public function addDelegate(EntityInterface $delegate);

    /**
     * @param EntityInterface $delegate A config object to prepend as delegate.
     * @return EntityInterface Chainable
     */
    public function prependDelegate(EntityInterface $delegate);
}
