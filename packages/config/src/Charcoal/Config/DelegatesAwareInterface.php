<?php

namespace Charcoal\Config;

/**
 * Describes an object that can perform lookups in other objects.
 *
 * This interface can be fully implemented with its accompanying {@see DelegatesAwareTrait}.
 */
interface DelegatesAwareInterface
{
    /**
     * Appends a collection of delegare objects.
     *
     * @param  EntityInterface[] $delegates One or more delegate objects to register.
     * @return DelegatesAwareInterface Chainable
     */
    public function setDelegates(array $delegates);

    /**
     * Appends a delegare object onto the delegate stack.
     *
     * @param  EntityInterface $delegate A delegate object to register.
     * @return DelegatesAwareInterface Chainable
     */
    public function addDelegate(EntityInterface $delegate);

    /**
     * Prepends a delegare object onto the delegate stack.
     *
     * @param  EntityInterface $delegate A delegate object to register.
     * @return DelegatesAwareInterface Chainable
     */
    public function prependDelegate(EntityInterface $delegate);
}
