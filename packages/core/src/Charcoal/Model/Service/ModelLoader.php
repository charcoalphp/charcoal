<?php

namespace Charcoal\Model\Service;

use ArrayAccess;
use LogicException;
use InvalidArgumentException;
// From PSR-6
use Psr\Cache\CacheItemPoolInterface;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

/**
 * Load a single model from its source, of from cache.
 *
 * Use the magic methods to load automatically from __call and __get; this allows
 * for easy integration in templating engines like Mustache.
 *
 * > This object is immutable.
 */
final class ModelLoader implements ArrayAccess
{
    /**
     * The object type.
     *
     * The base model (FQCN) to load objects of.
     *
     * @var string
     */
    private $objType;

    /**
     * The object key.
     *
     * The model property name, or SQL column name, to load objects by.
     *
     * @var string|null
     */
    private $objKey;

    /**
     * The model factory.
     *
     * @var FactoryInterface
     */
    private $factory;

    /**
     * The PSR-6 caching service.
     *
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * Construct a Model Loader with the dependencies
     *
     * # Required Dependencies
     *
     * - `obj_type`
     * - `factory`
     * - `cache`
     *
     * # Optional Dependencies
     *
     * - `obj_key`
     *
     * @param array $data Loader dependencies.
     */
    public function __construct(array $data)
    {
        $this->setCachePool($data['cache']);
        $this->setFactory($data['factory']);
        $this->setObjType($data['obj_type']);

        if (isset($data['obj_key'])) {
            $this->setObjKey($data['obj_key']);
        }
    }

    // Magic Methods
    // =============================================================================================

    /**
     * Retrieve an object by its key.
     *
     * @param  string|integer $ident The object identifier to load.
     * @param  mixed          $args  Unused; Method arguments.
     * @return ModelInterface
     */
    public function __call($ident, $args = null)
    {
        unset($args);

        return $this->load($ident);
    }

    /**
     * Retrieve an object by its key.
     *
     * @param  string|integer $ident The object identifier to load.
     * @return ModelInterface
     */
    public function __get($ident)
    {
        return $this->load($ident);
    }

    /**
     * Determine if an object exists by its key.
     *
     * @todo   Needs implementation
     * @param  string $ident The object identifier to lookup.
     * @return boolean
     */
    public function __isset($ident)
    {
        return true;
    }

    /**
     * Remove an object by its key.
     *
     * @param  string|integer $ident The object identifier to remove.
     * @throws LogicException This method should never be called.
     * @return void
     */
    public function __unset($ident)
    {
        throw new LogicException(
            'Can not unset value on a loader'
        );
    }

    // Satisfies ArrayAccess
    // =============================================================================================

    /**
     * Determine if an object exists by its key.
     *
     * @todo   Needs implementation
     * @see    ArrayAccess::offsetExists
     * @param  string $ident The object identifier to lookup.
     * @return boolean
     */
    public function offsetExists($ident)
    {
        return true;
    }

    /**
     * Retrieve an object by its key.
     *
     * @see    ArrayAccess::offsetGet
     * @param  string|integer $ident The object identifier to load.
     * @return ModelInterface
     */
    public function offsetGet($ident)
    {
        return $this->load($ident);
    }

    /**
     * Add an object.
     *
     * @see    ArrayAccess::offsetSet
     * @param  string|integer $ident The $object identifier.
     * @param  mixed          $obj   The object to add.
     * @throws LogicException This method should never be called.
     * @return void
     */
    public function offsetSet($ident, $obj)
    {
        throw new LogicException(
            'Can not set value on a loader'
        );
    }

    /**
     * Remove an object by its key.
     *
     * @see    ArrayAccess::offsetUnset()
     * @param  string|integer $ident The object identifier to remove.
     * @throws LogicException This method should never be called.
     * @return void
     */
    public function offsetUnset($ident)
    {
        throw new LogicException(
            'Can not unset value on a loader'
        );
    }

    // =============================================================================================

    /**
     * Retrieve an object, by its key, from its source or from the cache.
     *
     * When the cache is enabled, only the object's _data_ is stored. This prevents issues
     * when unserializing a class that might have dependencies.
     *
     * @param  string|integer $ident     The object identifier to load.
     * @param  boolean        $useCache  If FALSE, ignore the cached object. Defaults to TRUE.
     * @param  boolean        $reloadObj If TRUE, refresh the cached object. Defaults to FALSE.
     * @return ModelInterface
     */
    public function load($ident, $useCache = true, $reloadObj = false)
    {
        if (!$useCache) {
            return $this->loadFromSource($ident);
        }

        $cacheKey  = $this->cacheKey($ident);
        $cacheItem = $this->cachePool->getItem($cacheKey);

        if (!$reloadObj) {
            if ($cacheItem->isHit()) {
                $data = $cacheItem->get();
                $obj  = $this->factory->create($this->objType);
                $obj->setData($data);

                return $obj;
            }
        }

        $obj  = $this->loadFromSource($ident);
        $data = ($obj->id() ? $obj->data() : []);
        $cacheItem->set($data);
        $this->cachePool->save($cacheItem);

        return $obj;
    }

    /**
     * Load an objet from its soure.
     *
     * @param  string|integer $ident The object identifier to load.
     * @return ModelInterface
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
     * Generate a cache key.
     *
     * @param  string|integer $ident The object identifier to load.
     * @return string
     */
    private function cacheKey($ident)
    {
        if ($this->objKey === null) {
            $model = $this->factory->get($this->objType);
            $this->setObjKey($model->key());
        }

        $cacheKey = 'object/' . str_replace('/', '.', $this->objType . '.' . $this->objKey . '.' . $ident);

        return $cacheKey;
    }

    /**
     * Set the object type.
     *
     * Based on {@see DescribableTrait::generateMetadataIdent()}.
     *
     * @param  string $objType The object type to load with this loader.
     * @throws InvalidArgumentException If the object type is not a string.
     * @return self
     */
    private function setObjType($objType)
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(
                'Can not set model loader object type: not a string'
            );
        }

        $objType = preg_replace('/([a-z])([A-Z])/', '$1-$2', $objType);
        $objType = strtolower(str_replace('\\', '/', trim($objType, '\\/')));

        $this->objType = $objType;
        return $this;
    }

    /**
     * Set the object key.
     *
     * @param  string $objKey The object key to use for laoding.
     * @throws InvalidArgumentException If the object key is not a string.
     * @return self
     */
    private function setObjKey($objKey)
    {
        if (empty($objKey) && !is_numeric($objKey)) {
            $this->objKey = null;
            return $this;
        }

        if (!is_string($objKey)) {
            throw new InvalidArgumentException(
                'Can not set model loader object type: not a string'
            );
        }

        $this->objKey = $objKey;
        return $this;
    }

    /**
     * Set the model factory.
     *
     * @param  FactoryInterface $factory The factory to create models.
     * @return self
     */
    private function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * Set the cache pool handler.
     *
     * @param  CacheItemPoolInterface $cachePool A PSR-6 compatible cache pool.
     * @return self
     */
    private function setCachePool(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
        return $this;
    }
}
