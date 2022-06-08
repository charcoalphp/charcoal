<?php

namespace Charcoal\Source;

use InvalidArgumentException;

/**
 * Describes a filter expression tree.
 */
interface FilterCollectionInterface
{
    /**
     * Replace the query filter(s) on this object.
     *
     * Note: Any existing filters are dropped.
     *
     * @param  array $filters One or more filters to set on this expression.
     * @return FilterCollectionInterface Returns the current expression.
     */
    public function setFilters(array $filters);

    /**
     * Append one or more query filters on this object.
     *
     * @param  array $filters One or more filters to add on this expression.
     * @return FilterCollectionInterface Returns the current expression.
     */
    public function addFilters(array $filters);

    /**
     * Append a query filter on this object.
     *
     * @param  mixed $filter The filter expression string, structure, object, or callable to append.
     * @throws InvalidArgumentException If the filter is invalid.
     * @return FilterCollectionInterface Returns the current expression.
     */
    public function addFilter($filter);

    /**
     * Determine if the object has any query filters.
     *
     * @return boolean
     */
    public function hasFilters();

    /**
     * Retrieve the query filters stored in this object.
     *
     * @return array
     */
    public function filters();
}
