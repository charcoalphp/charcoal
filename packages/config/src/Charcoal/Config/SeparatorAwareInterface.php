<?php

namespace Charcoal\Config;

/**
 * Describes an object that can perform lookups into multi-dimensional arrays.
 *
 * This interface can be fully implemented with its accompanying {@see SeparatorAwareTrait}.
 */
interface SeparatorAwareInterface
{
    /**
     * Sets the token for traversing a data-tree.
     *
     * @param  string $separator The token to delimit nested data.
     * @return SeparatorAwareInterface Chainable
     */
    public function setSeparator($separator);
}
