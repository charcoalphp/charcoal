<?php

namespace Charcoal\Model\Service;

use \Psr\Cache\CacheItemPoolInterface;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\FactoryInterface;

use \Charcoal\Model\Service\ModelLoader;

/**
 * Model Loader Builder.
 *
 * Build custom ModelLoader objects with a certain obj type / optional obj key.
 */
final class ModelLoaderBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * @param array $data The constructor options (factory and cache).
     */
    public function __construct(array $data)
    {
        $this->setFactory($data['factory']);
        $this->setCachePool($data['cache']);
    }

    /**
     * @param string $objType The object type of the ModelLoader.
     * @param string $objKey  Optional object key, to set on the ModelLoader.
     * @return ModelLoader
     */
    public function build($objType, $objKey = null)
    {
        return new ModelLoader([
            'factory' => $this->factory,
            'cache'     => $this->cachePool,
            'obj_type'  => $objType,
            'obj_key'   => $objKey
        ]);
    }

    /**
     * The builder can be invoked (used as function).
     *
     * @param string $objType The object type of the ModelLoader.
     * @param string $objKey  Optional object key, to set on the ModelLoader.
     * @return ModelLoader
     */
    public function __invoke($objType, $objKey = null)
    {
        return $this->build($objType, $objKey);
    }

    /**
     * @param FactoryInterface $factory The factory to use to create models.
     * @return ModelLoaderBuilder Chainable
     */
    private function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @param CacheItemPoolInterface $cachePool The PSR-6 compliant cache pool.
     * @return ModelLoaderBuilder Chainable
     */
    private function setCachePool(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
        return $this;
    }
}
