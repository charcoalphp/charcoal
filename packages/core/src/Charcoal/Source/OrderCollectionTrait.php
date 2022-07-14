<?php

namespace Charcoal\Source;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Source\Order;
use Charcoal\Source\OrderInterface;
use Charcoal\Source\OrderCollectionInterface;

/**
 * Provides a order expression list.
 *
 * Satisfies {@see OrderCollectionInterface}.
 */
trait OrderCollectionTrait
{
    /**
     * The list of query sorting objects.
     *
     * For example: one key of the array might look like "RAND()"
     * or `{ "property": "col", "direction": "ASC" }`.
     *
     * @var OrderInterface[]
     */
    protected $orders = [];

    /**
     * Replace the query order(s) on this object.
     *
     * Note: Any existing orders are dropped.
     *
     * @param  mixed[] $orders One or more orders to set on this expression.
     * @return self
     */
    public function setOrders(array $orders)
    {
        $this->orders = [];
        $this->addOrders($orders);
        return $this;
    }

    /**
     * Append one or more query orders on this object.
     *
     * @uses   self::processOrder()
     * @param  mixed[] $orders One or more orders to add on this expression.
     * @return self
     */
    public function addOrders(array $orders)
    {
        foreach ($orders as $key => $order) {
            $this->addOrder($order);

            /** Name the expression if $key is a non-numeric string. */
            if (is_string($key) && !is_numeric($key)) {
                $order = end($this->orders);
                $order->setName($key);
            }
        }

        return $this;
    }

    /**
     * Append a query order on this object.
     *
     * @uses   self::processOrder()
     * @param  mixed $order The expression string, structure, object, or callable to be parsed.
     * @return self
     */
    public function addOrder($order)
    {
        $this->orders[] = $this->processOrder($order);
        return $this;
    }

    /**
     * Process a query order to build a tree of expressions.
     *
     * Implement in subclasses to dynamically parse orders before being appended.
     *
     * @param  mixed $order The expression string, structure, object, or callable to be parsed.
     * @throws InvalidArgumentException If a order is not a string, array, object, or callable.
     * @return OrderInterface
     */
    protected function processOrder($order)
    {
        if (!is_string($order) && is_callable($order)) {
            $expr  = $this->createOrder();
            /**
             * @param  OrderInterface           $expr The new order expression object.
             * @param  OrderCollectionInterface $this The context of the collection.
             * @return string|array|OrderInterface The prepared order expression
             *     string, structure, object.
             */
            $order = $order($expr, $this);
        }

        if (is_string($order)) {
            $expr  = $this->createOrder()->setCondition($order);
            $order = $expr;
        } elseif (is_array($order)) {
            $expr  = $this->createOrder()->setData($order);
            $order = $expr;
        }

        /** Append the order to the expression's stack. */
        if ($order instanceof OrderInterface) {
            return $order;
        }

        throw new InvalidArgumentException(sprintf(
            'Order must be a string, structure, or Expression object; received %s',
            is_object($order) ? get_class($order) : gettype($order)
        ));
    }

    /**
     * Determine if the object has any query orders.
     *
     * @return boolean
     */
    public function hasOrders()
    {
        return !empty($this->orders);
    }

    /**
     * Retrieve the query orders stored in this object.
     *
     * @return array
     */
    public function orders()
    {
        return $this->orders;
    }

    /**
     * Traverses the tree of query orders and applies a user function to every expression.
     *
     * @param  callable $callable The function to run for each expression.
     * @return self
     */
    public function traverseOrders(callable $callable)
    {
        foreach ($this->orders() as $expr) {
            /**
             * @param  OrderInterface           $expr The iterated order expression object.
             * @param  OrderCollectionInterface $this The context of the traversal.
             * @return void
             */
            $callable($expr, $this);
            if ($expr instanceof OrderCollectionInterface) {
                $expr->traverseOrders($callable);
            }
        }

        return $this;
    }

    /**
     * Create a new query sorting expression.
     *
     * @param  array $data Optional expression data.
     * @return OrderInterface A new order expression object.
     */
    abstract protected function createOrder(array $data = null);
}
