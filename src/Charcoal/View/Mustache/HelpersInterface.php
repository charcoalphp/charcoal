<?php

namespace Charcoal\View\Mustache;

/**
 * Defines a collection of Mustache helpers.
 *
 * > An array of 'helpers'. Helpers can be global variables or objects, closures
 * > (e.g. for higher order sections), or any other valid Mustache context value.
 * > They will be prepended to the context stack, so they will be available in
 * > any template loaded by this Mustache instance.
 * — {@link https://github.com/bobthecow/mustache.php/wiki#helpers}
 */
interface HelpersInterface
{
    /**
     * Get the collection of helpers as a plain array.
     *
     * @return array
     */
    public function toArray();
}
