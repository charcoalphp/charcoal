<?php

namespace Charcoal\Tests\Mock;

// From 'charcoal-core'
use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;
use Charcoal\Source\FilterCollectionTrait;
use Charcoal\Source\FilterCollectionInterface;

/**
 * Mock Filter Collection.
 */
class FilterCollectionClass implements
    FilterCollectionInterface
{
    use FilterCollectionTrait;

    /**
     * Create a new query filter expression.
     *
     * @see    FilterCollectionTrait::createFilter()
     * @param  array $data Optional expression data.
     * @return FilterInterface
     */
    protected function createFilter(array $data = null)
    {
        $filter = new Filter();
        if ($data !== null) {
            $filter->setData($data);
        }
        return $filter;
    }
}
