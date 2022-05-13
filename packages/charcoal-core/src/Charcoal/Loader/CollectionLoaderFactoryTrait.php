<?php

namespace Charcoal\Loader;

use RuntimeException;
use Charcoal\Factory\FactoryInterface;

/**
 * Provides factoring for collection loader.
 *
 * Collection Loader Factory Trait
 * @package Charcoal\Loader
 */
trait CollectionLoaderFactoryTrait
{
    /**
     * Store the factory instance.
     *
     * @var FactoryInterface
     */
    protected $collectionLoaderFactory;

    /**
     * Set a model collection loader factory.
     *
     * @param  FactoryInterface $factory The factory to create model collection loaders.
     * @return void
     */
    protected function setCollectionLoaderFactory(FactoryInterface $factory)
    {
        $this->collectionLoaderFactory = $factory;
    }

    /**
     * Retrieve the collection loader factory.
     *
     * @throws RuntimeException If the collection loader factory is missing.
     * @return FactoryInterface
     */
    public function collectionLoaderFactory()
    {
        if (!isset($this->collectionLoaderFactory)) {
            throw new RuntimeException(sprintf(
                'Collection Loader Factory is not defined for [%s]',
                get_class($this)
            ));
        }

        return $this->collectionLoaderFactory;
    }

    /**
     * Create a model collection loader with optional constructor arguments and a post-creation callback.
     *
     * @param  array|null    $args     Optional. Constructor arguments.
     * @param  callable|null $callback Optional. Called at creation.
     * @return CollectionLoader
     */
    public function createCollectionLoaderWith(array $args = null, callable $callback = null)
    {
        $factory = $this->collectionLoaderFactory();

        return $factory->create($factory->defaultClass(), $args, $callback);
    }

    /**
     * Create a model collection loader.
     *
     * @return CollectionLoader
     */
    public function createCollectionLoader()
    {
        $factory = $this->collectionLoaderFactory();

        return $factory->create($factory->defaultClass());
    }
}
