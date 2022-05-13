<?php

namespace Charcoal\Loader;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

/**
 * Lazy Object Collection Loader
 */
class LazyCollectionLoader extends CollectionLoader
{
    /**
     * Process the collection of raw data.
     *
     * @param  mixed[]|Traversable $results The raw result set.
     * @param  callable|null       $before  Process each entity before applying raw data.
     * @param  callable|null       $after   Process each entity after applying raw data.
     * @return ModelInterface[]|\Generator
     */
    protected function processCollection($results, callable $before = null, callable $after = null)
    {
        foreach ($results as $objData) {
            $obj = $this->processModel($objData, $before, $after);

            if ($obj instanceof ModelInterface) {
                yield $obj;
            }
        }
    }
}
