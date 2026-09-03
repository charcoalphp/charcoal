<?php

namespace Charcoal\Source;

/**
 * Sanitizes filter/order expression trees from untrusted storage or request config.
 *
 * Strips raw SQL escape hatches (`condition`, deprecated `string`, bare SQL strings)
 * so second-order injection cannot reintroduce LS02 via setData/setFilters.
 * Structured property/operator/value predicates remain (LS01 binds; LS04 identifiers).
 */
final class ExpressionTreeSanitizer
{
    /**
     * Sanitize a list/tree of filter structures.
     *
     * @param  array $filters Filter structures (possibly nested).
     * @return array
     */
    public static function sanitizeFilters(array $filters)
    {
        foreach ($filters as $key => $filter) {
            if (!is_array($filter)) {
                // Bare SQL strings are not accepted from untrusted input.
                unset($filters[$key]);
                continue;
            }

            unset($filter['condition'], $filter['string']);

            if (isset($filter['filters']) && is_array($filter['filters'])) {
                $filter['filters'] = self::sanitizeFilters($filter['filters']);
            }

            if (isset($filter['conditions']) && is_array($filter['conditions'])) {
                $filter['conditions'] = self::sanitizeFilters($filter['conditions']);
            }

            if (!self::filterHasUsableCriteria($filter)) {
                unset($filters[$key]);
                continue;
            }

            $filters[$key] = $filter;
        }

        return array_values($filters);
    }

    /**
     * Sanitize a list of order structures.
     *
     * @param  array $orders Order structures.
     * @return array
     */
    public static function sanitizeOrders(array $orders)
    {
        foreach ($orders as $key => $order) {
            if (!is_array($order)) {
                unset($orders[$key]);
                continue;
            }

            unset($order['condition'], $order['string']);

            // Custom mode without a trusted condition is not usable.
            if (isset($order['mode']) && strtolower((string)$order['mode']) === 'custom') {
                unset($order['mode']);
            }

            if (!self::orderHasUsableCriteria($order)) {
                unset($orders[$key]);
                continue;
            }

            $orders[$key] = $order;
        }

        return array_values($orders);
    }

    /**
     * @param  array $filter A filter structure after stripping raw SQL keys.
     * @return boolean
     */
    private static function filterHasUsableCriteria(array $filter)
    {
        return isset($filter['property'])
            || array_key_exists('value', $filter)
            || !empty($filter['filters'])
            || !empty($filter['conditions']);
    }

    /**
     * @param  array $order An order structure after stripping raw SQL keys.
     * @return boolean
     */
    private static function orderHasUsableCriteria(array $order)
    {
        return isset($order['property'])
            || isset($order['mode'])
            || !empty($order['values'])
            || isset($order['direction']);
    }
}
