<?php

namespace Charcoal\Source;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;
use Charcoal\Source\FilterCollectionInterface;

/**
 * Provides a filter expression tree.
 *
 * Satisfies {@see FilterCollectionInterface}.
 */
trait FilterCollectionTrait
{
    /**
     * The tree of query filter objects.
     *
     * For example: one key of the array might look like "col LIKE :value"
     * or `{ "property": "col", "value": 10, "operator": "LIKE" }`.
     *
     * @var FilterInterface[]
     */
    protected $filters = [];

    /**
     * Replace the query filter(s) on this object.
     *
     * Note: Any existing filters are dropped.
     *
     * @param  mixed[] $filters One or more filters to set on this expression.
     * @return self
     */
    public function setFilters(array $filters)
    {
        $this->filters = [];
        $this->addFilters($filters);
        return $this;
    }

    /**
     * Append one or more query filters on this object.
     *
     * @uses   self::processFilter()
     * @param  mixed[] $filters One or more filters to add on this expression.
     * @return self
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $key => $filter) {
            $this->addFilter($filter);

            /** Name the expression if $key is a non-numeric string. */
            if (is_string($key) && !is_numeric($key)) {
                $filter = end($this->filters);
                $filter->setName($key);
            }
        }

        return $this;
    }

    /**
     * Append a query filter on this object.
     *
     * @uses   self::processFilter()
     * @param  mixed $filter The expression string, structure, object, or callable to be parsed.
     * @return self
     */
    public function addFilter($filter)
    {
        $this->filters[] = $this->processFilter($filter);
        return $this;
    }

    /**
     * Process a query filter to build a tree of expressions.
     *
     * Implement in subclasses to dynamically parse filters before being appended.
     *
     * @param  mixed $filter The expression string, structure, object, or callable to be parsed.
     * @throws InvalidArgumentException If a filter is not a string, array, object, or callable.
     * @return FilterInterface
     */
    protected function processFilter($filter)
    {
        if (!is_string($filter) && is_callable($filter)) {
            $expr   = $this->createFilter();
            /**
             * @param  FilterInterface           $expr The new filter expression object.
             * @param  FilterCollectionInterface $this The context of the collection.
             * @return string|array|FilterInterface The prepared filter expression
             *     string, structure, object.
             */
            $filter = $filter($expr, $this);
        }

        if (is_string($filter)) {
            $expr   = $this->createFilter()->setCondition($filter);
            $filter = $expr;
        } elseif (is_array($filter)) {
            $expr   = $this->createFilter()->setData($filter);
            $filter = $expr;
        }

        /** Append the filter to the expression's stack. */
        if ($filter instanceof FilterInterface) {
            return $filter;
        }

        throw new InvalidArgumentException(sprintf(
            'Filter must be a string, structure, or Expression object; received %s',
            is_object($filter) ? get_class($filter) : gettype($filter)
        ));
    }

    /**
     * Determine if the object has any query filters.
     *
     * @return boolean
     */
    public function hasFilters()
    {
        return !empty($this->filters);
    }

    /**
     * Retrieve the query filters stored in this object.
     *
     * @return array
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     * Traverses the tree of query filters and applies a user function to every expression.
     *
     * @param  callable $callable The function to run for each expression.
     * @return self
     */
    public function traverseFilters(callable $callable)
    {
        foreach ($this->filters() as $expr) {
            /**
             * @param  FilterInterface           $expr The iterated filter expression object.
             * @param  FilterCollectionInterface $this The context of the traversal.
             * @return void
             */
            $callable($expr, $this);
            if ($expr instanceof FilterCollectionInterface) {
                $expr->traverseFilters($callable);
            }
        }

        return $this;
    }

    /**
     * Create a new query filter expression.
     *
     * @param  array $data Optional expression data.
     * @return FilterInterface A new filter expression object.
     */
    abstract protected function createFilter(array $data = null);
}
