<?php

namespace Charcoal\Model;

use \ArrayAccess;
use \InvalidArgumentException;

use \Psr\Cache\CacheItemPoolInterface;

use \Charcoal\Factory\FactoryInterface;

/**
 * Load a single model from its source, of from cache.
 *
 * Use the magic methods to load automatically from __call and __get; this allows
 * for easy integration in templating engines like Mustache.
 */
class ModelLoader implements ArrayAccess
{
    /**
     * @var string $objType
     */
    private $objType;

    /**
     * @var string $objKey
     */
    private $objKey;

    /**
     * @var FactoryInterface $factory
     */
    private $factory;

    /**
     * @var CacheItemPoolInterface $cachePool
     */
    private $cachePool;

    /**
     * Construct a Model Loader with the dependencies
     *
     * # Required dependencies
     *
     * - `obj_type`
     * - `factory`
     * - `cache`
     *
     * # Optional dependencies
     * - `obj_key`
     *
     * @param array $data Class dependencies.
     */
    public function __construct(array $data)
    {
        $this->setObjType($data['obj_type']);
        $this->setFactory($data['factory']);
        $this->setCachePool($data['cache']);

        if (isset($data['obj_key'])) {
            $this->setObjKey($data['obj_key']);
        }
    }

    /**
     * @param string $ident The object ident to load.
     * @param mixed  $args  Unused, method arguments.
     * @return \Charcoal\Model\ModelInterface
     */
    public function __call($ident, $args = null)
    {
        unset($args);
        return $this->load($ident);
    }

    /**
     * @param string $ident The object ident to load.
     * @return \Charcoal\Model\ModelInterface
     */
    public function __get($ident)
    {
        return $this->load($ident);
    }


    /**
     * @param string $ident The object ident to load.
     * @return boolean
     */
    public function __isset($ident)
    {
        // TODO
        return true;
    }

    /**
     * @param string $ident The object ident to unset.
     * @throws Exception This method should never be called.
     * @return void
     */
    public function __unset($ident)
    {
        throw new Exception(
            'Can not unset value on a loader'
        );
    }

    /**
     * @param string $ident The object ident to verify.
     * @return boolean
     */
    public function offsetExists($ident)
    {
        // TODO
        return true;
    }

    /**
     * @param string $ident The object ident to load.
     * @return \Charcoal\Model\ModelInterface
     */
    public function offsetGet($ident)
    {
        return $this->load($ident);
    }

    /**
     * @param string $ident The object ident to set.
     * @param mixed  $val   The value to set.
     * @throws Exception This method should never be called.
     * @return void
     */
    public function offsetSet($ident, $val)
    {
        throw new Exception(
            'Can not set value on a loader'
        );
    }

    /**
     * @param string $ident The object ident to unset.
     * @throws Exception This method should never be called.
     * @return void
     */
    public function offsetUnset($ident)
    {
        throw new Exception(
            'Can not unset value on a loader'
        );
    }

    /**
     * @param string  $ident    The object ident to load.
     * @param boolean $useCache Optional (default to true). Set to false to force a reload (skip cache).
     * @return \Charcoal\Model\ModelInterface
     */
    public function load($ident, $useCache = true)
    {
        if (!$useCache) {
            // Do not use cache;
            return $this->loadFromSource($ident);
        }
        $cacheItem = $this->cachePool->getItem('model/'.$this->objType.'/'.$ident);

        $obj = $cacheItem->get();
        if ($cacheItem->isMiss()) {
            $cacheItem->lock();

            $obj = $this->loadFromSource($ident);

            $this->cachePool->save($cacheItem->set($obj));
        }
        return $obj;
    }

    /**
     * Load an objet from soure
     *
     * @param mixed $ident The object ident to load.
     * @return \Charcoal\Model\ModelInterface
     */
    private function loadFromSource($ident)
    {
        $obj = $this->factory->create($this->objType);
        if ($this->objKey) {
            $obj->loadFrom($this->objKey, $ident);
        } else {
            $obj->load($ident);
        }
        return $obj;
    }

    /**
     * @param string $objType The object type to load with this loader.
     * @throws InvalidArgumentException If the obj type is not a string.
     * @return ModelLoader Chainable
     */
    private function setObjType($objType)
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(
                'Can not set model loader object type: not a string'
            );
        }
        $this->objType = $objType;
        return $this;
    }

    /**
     * @param string $objKey The object key to use for laoding.
     * @throws InvalidArgumentException If the obj key is not a string.
     * @return ModelLoader Chainable
     */
    private function setObjKey($objKey)
    {
        if (!is_string($objKey)) {
            throw new InvalidArgumentException(
                'Can not set model loader object type: not a string'
            );
        }
        $this->objKey = $objKey;
        return $this;
    }

    /**
     * @param FactoryInterface $factory The factory to use to create models.
     * @return ModelLoader Chainable
     */
    private function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @param CacheItemPoolInterface $cachePool The PSR-6 compliant cache pool.
     * @return ModelLoader Chainable
     */
    private function setCachePool(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
        return $this;
    }
}
