<?php

namespace Charcoal\Loader;

use RuntimeException;
use Charcoal\Loader\CollectionLoader;

/**
 * Provides model collection features.
 *
 * Collection Loader Loader Aware Trait
 * @package Charcoal\Loader
 */
trait CollectionLoaderAwareTrait
{
    /**
     * Store the collection loader.
     *
     * @var CollectionLoader
     */
    protected $collectionLoader;

    /**
     * Set a model collection loader.
     *
     * @param  CollectionLoader $loader The model collection loader.
     * @return void
     */
    protected function setCollectionLoader(CollectionLoader $loader)
    {
        $this->collectionLoader = $loader;
    }

    /**
     * Retrieve the model collection loader.
     *
     * @throws RuntimeException If the collection loader is missing.
     * @return CollectionLoader
     */
    public function collectionLoader()
    {
        if (!isset($this->collectionLoader)) {
            throw new RuntimeException(sprintf(
                'Collection Loader is not defined for [%s]',
                get_class($this)
            ));
        }

        return $this->collectionLoader;
    }
}
