<?php

namespace Charcoal\Tests\Mock;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\OrderCollectionInterface;
use Charcoal\Source\OrderCollectionTrait;
use Charcoal\Source\OrderInterface;
use Charcoal\Source\Order as BaseOrder;

/**
 * Nestable Order Expression
 */
class OrderTree extends BaseOrder implements
    OrderCollectionInterface
{
    use OrderCollectionTrait;

    /**
     * Set the order clause data.
     *
     * @param  array<string,mixed> $data The expression data;
     *     as an associative array.
     */
    #[\Override]
    public function setData(array $data): static
    {
        parent::setData($data);

        if (isset($data['orders'])) {
            $this->addOrders($data['orders']);
        }

        return $this;
    }

    /**
     * Retrieve the default values for ordering.
     *
     * @return array<string,mixed> An associative array.
     */
    #[\Override]
    public function defaultData(): array
    {
        return [
            'property'  => null,
            'table'     => null,
            'direction' => null,
            'mode'      => null,
            'values'    => null,
            'orders'    => [],
            'condition' => null,
            'active'    => true,
            'name'      => null,
        ];
    }

    /**
     * Retrieve the order clause structure.
     *
     * @return array<string,mixed> An associative array.
     */
    #[\Override]
    public function data(): array
    {
        return [
            'property'  => $this->property(),
            'table'     => $this->table(),
            'direction' => $this->direction(),
            'mode'      => $this->mode(),
            'values'    => $this->values(),
            'orders'    => $this->orders(),
            'condition' => $this->condition(),
            'active'    => $this->active(),
            'name'      => $this->name(),
        ];
    }

    /**
     * Create a new order expression.
     *
     * @see    OrderCollectionTrait::createOrder()
     * @param  array $data Optional expression data.
     */
    protected function createOrder(?array $data = null): static
    {
        $order = new static();
        if ($data !== null) {
            $order->setData($data);
        }
        return $order;
    }

    /**
     * Alias of {@see self::traverseOrders()}
     *
     * @param  callable $callable The function to run for each expression.
     * @return self
     */
    public function traverse(callable $callable)
    {
        return $this->traverseOrders($callable);
    }

    /**
     * Clone this expression and its subtree of expressions.
     */
    public function __clone()
    {
        foreach ($this->orders as $i => $order) {
            if ($order instanceof ExpressionInterface) {
                $this->orders[$i] = clone $order;
            }
        }
    }
}
