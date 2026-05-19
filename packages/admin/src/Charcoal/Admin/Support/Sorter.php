<?php

declare(strict_types=1);

namespace Charcoal\Admin\Support;

/**
 * A collection of static sorter functions.
 */
class Sorter
{
    /**
     * Compares the priority attribute of two array-accessible variables
     * to determine if the first variable is considered to be respectively
     * less than, equal to, or greater than the second.
     *
     * To be called with {@see uasort()}.
     *
     * @param  array|ArrayAccess $a
     * @param  array|ArrayAccess $b
     */
    public static function sortByPriority($a, $b): int
    {
        $a = ($a['priority'] ?? 0);
        $b = ($b['priority'] ?? 0);
        return $a <=> $b;
    }
}
