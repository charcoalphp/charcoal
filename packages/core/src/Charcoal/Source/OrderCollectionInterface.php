<?php

namespace Charcoal\Source;

use InvalidArgumentException;

/**
 * Describes an order expression list.
 */
interface OrderCollectionInterface
{
    /**
     * Replace the query order(s) on this object.
     *
     * Note: Any existing orders are dropped.
     *
     * @param  array   $orders   One or more orders to set on this expression.
     * @param  boolean $trusted  TRUE allows raw condition SQL; FALSE sanitizes untrusted lists.
     * @return self
     */
    public function setOrders(array $orders, $trusted = true);

    /**
     * Append one or more query orders on this object.
     *
     * @param  array   $orders   One or more orders to add on this expression.
     * @param  boolean $trusted  TRUE allows raw condition SQL; FALSE sanitizes untrusted lists.
     * @return self
     */
    public function addOrders(array $orders, $trusted = true);

    /**
     * Append a query order on this object.
     *
     * @param  mixed $order The order expression string, structure, object, or callable to append.
     * @throws InvalidArgumentException If the order is invalid.
     * @return self
     */
    public function addOrder($order);

    /**
     * Determine if the object has any query orders.
     *
     * @return boolean
     */
    public function hasOrders();

    /**
     * Retrieve the query orders stored in this object.
     *
     * @return array
     */
    public function orders();
}
