<?php

namespace Charcoal\Tests\Mock;

// From 'charcoal-core'
use Charcoal\Source\Order;
use Charcoal\Source\OrderInterface;
use Charcoal\Source\OrderCollectionTrait;
use Charcoal\Source\OrderCollectionInterface;

/**
 * Mock Order Collection.
 */
class OrderCollectionClass implements
    OrderCollectionInterface
{
    use OrderCollectionTrait;

    /**
     * Create a new query order expression.
     *
     * @see    OrderCollectionTrait::createOrder()
     * @param  array $data Optional expression data.
     * @return OrderInterface
     */
    protected function createOrder(array $data = null)
    {
        $order = new Order();
        if ($data !== null) {
            $order->setData($data);
        }
        return $order;
    }
}
