<?php

declare(strict_types=1);

namespace Charcoal\View\Twig;

/**
 * Defines a collection of Twig helpers.
 *
 * > They will be prepended to the context stack, so they will be available in
 * > any template loaded by this Twig instance.
 * — {@link https://twig.symfony.com/doc/3.x/advanced.html#creating-an-extension}
 */
interface HelpersInterface
{
    /**
     * Get the collection of helpers as a plain array.
     *
     * @return array
     */
    public function toArray(): array;
}
