<?php

namespace Charcoal\Cache;

use ArrayAccess;
use Traversable;
use InvalidArgumentException;
// From 'tedivm/stash'
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;
use Stash\Interfaces\DriverInterface;
use Stash\Pool;

/**
 * Cache Pool Builder
 *
 * Build custom PSR-6 cache pools using Stash drivers.
 */
final class CacheBuilder
{
    /**
     * List of available cache driver names and classes.
     *
     * @var ArrayAccess|array
     */
    private $drivers;

    /**
     * Default logger instance.
     *
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    /**
     * Default namespace for new pools.
     *
     * @var string|null
     */
    private $namespace;

    /**
     * Default "Pool" class to use for making new pools.
     *
     * @var string
     */
    private $poolClass = Pool::class;

    /**
     * Default "Item" class to use for making new items.
     *
     * @var string|null
     */
    private $itemClass;

    /**
     * Create a cache pool builder.
     *
     * @param array $data The cache builder dependencies.
     */
    public function __construct(array $data)
    {
        $this->setDrivers($data['drivers']);

        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }

        if (isset($data['namespace'])) {
            $this->setNamespace($data['namespace']);
        }

        if (isset($data['pool_class'])) {
            $this->setPoolClass($data['pool_class']);
        }

        if (isset($data['item_class'])) {
            $this->setItemClass($data['item_class']);
        }
    }

    /**
     * Invoke a new cache pool.
     *
     * @param  mixed $cacheDriver The name of a registered cache driver,
     *     the class name or instance of a {@see DriverInterface cache driver}.
     *     An array may be used to designate fallback drivers.
     * @param  mixed $poolOptions Optional settings for the new pool.
     * @return PoolInterface
     */
    public function __invoke($cacheDriver, $poolOptions = null)
    {
        return $this->build($cacheDriver, $poolOptions);
    }

    /**
     * Create a new cache pool.
     *
     * @param  mixed $cacheDriver The name of a registered cache driver,
     *     the class name or instance of a {@see DriverInterface cache driver}.
     *     An array may be used to designate fallback drivers.
     * @param  mixed $poolOptions Optional settings for the new pool.
     *     If a string is specified, it is used as the namespace for the new pool.
     *     If an array is specified, it is assumed to be associative and is merged with the default settings.
     *     Otherwise, the default settings are used.
     * @return PoolInterface
     */
    public function build($cacheDriver, $poolOptions = null)
    {
        if (!($cacheDriver instanceof DriverInterface)) {
            $cacheDriver = $this->resolveDriver($cacheDriver);
        }

        $poolOptions  = $this->parsePoolOptions($poolOptions);
        $poolInstance = new $poolOptions['pool_class']($cacheDriver);

        $this->applyPoolOptions($poolInstance, $poolOptions);

        return $poolInstance;
    }

    /**
     * Prepare any pool options for the new pool object.
     *
     * @param  mixed $options Settings for the new pool.
     * @return array
     */
    private function parsePoolOptions($options)
    {
        $defaults = [
            'pool_class' => $this->poolClass,
            'item_class' => $this->itemClass,
            'namespace'  => $this->namespace,
            'logger'     => $this->logger,
        ];

        if ($options === null) {
            return $defaults;
        }

        if (is_string($options)) {
            $options = [
                'namespace' => $options
            ];
        }

        if (!is_array($options)) {
            return $defaults;
        }

        return array_replace($defaults, $options);
    }

    /**
     * Apply any pool options on the new pool object.
     *
     * @param  PoolInterface $pool    The new pool.
     * @param  array         $options Settings for the new pool.
     * @return void
     */
    private function applyPoolOptions(PoolInterface $pool, array $options)
    {
        if (isset($options['logger'])) {
            $pool->setLogger($options['logger']);
        }

        if (isset($options['item_class'])) {
            $pool->setItemClass($options['item_class']);
        }

        if (isset($options['namespace'])) {
            $pool->setNamespace($options['namespace']);
        }
    }

    /**
     * Resolve one or many cache drivers, if available.
     *
     * @param  mixed $driver The name of a registered cache driver,
     *     the class name or instance of a {@see DriverInterface cache driver}.
     *     An array may be used to designate fallback drivers.
     * @throws InvalidArgumentException When an array of drivers cannot be resolved.
     * @return DriverInterface
     */
    private function resolveDriver($driver)
    {
        if ($this->isIterable($driver)) {
            foreach ($driver as $drv) {
                try {
                    return $this->resolveOneDriver($drv);
                } catch (InvalidArgumentException $e) {
                    continue;
                }
            }

            throw new InvalidArgumentException(
                'Drivers cannot be resolved'
            );
        }

        return $this->resolveOneDriver($driver);
    }

    /**
     * Resolve the given cache driver, if available.
     *
     * @param  mixed $driver The name of a registered cache driver,
     *     the class name or instance of a {@see DriverInterface cache driver}.
     * @throws InvalidArgumentException When passed an invalid or nonexistant driver name, class name, or object.
     * @return DriverInterface
     */
    private function resolveOneDriver($driver)
    {
        if (empty($driver)) {
            throw new InvalidArgumentException(
                'Driver is empty'
            );
        }

        if (is_object($driver)) {
            if ($driver instanceof DriverInterface) {
                return $driver;
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Driver class %s must implement %s',
                    get_class($driver),
                    DriverInterface::class
                ));
            }
        }

        $name = $driver;
        if (isset($this->drivers[$name])) {
            $driver = $this->drivers[$name];

            if (empty($driver)) {
                throw new InvalidArgumentException(
                    sprintf('Driver "%s" does not exist', $name)
                );
            }

            if (is_object($driver)) {
                if ($driver instanceof DriverInterface) {
                    return $driver;
                } else {
                    throw new InvalidArgumentException(sprintf(
                        'Driver "%s": Class %s must implement %s',
                        $name,
                        get_class($driver),
                        DriverInterface::class
                    ));
                }
            }
        }

        if (is_a($driver, DriverInterface::class, true)) {
            return new $driver();
        }

        throw new InvalidArgumentException(
            sprintf('Driver "%s" cannot be resolved', $name)
        );
    }

    /**
     * Sets the collection of available cache drivers.
     *
     * @param  ArrayAccess|array $drivers The driver list used to create cache drivers.
     * @throws InvalidArgumentException If the drivers list is invalid.
     * @return void
     */
    private function setDrivers($drivers)
    {
        if ($this->isAccessible($drivers)) {
            $this->drivers = $drivers;
        } else {
            throw new InvalidArgumentException(
                'Driver list must be an accessible array'
            );
        }
    }

    /**
     * Sets the specific PSR logging client to enable the tracking of errors.
     *
     * @param  \Psr\Log\LoggerInterface $logger A PSR-3 logger.
     * @throws InvalidArgumentException If the logger is invalid PSR-3 client.
     * @return void
     */
    private function setLogger($logger)
    {
        $psr = 'Psr\\Log\\LoggerInterface';
        if (!is_a($logger, $psr)) {
            throw new InvalidArgumentException(
                sprintf('Expected an instance of %s', $psr)
            );
        }

        $this->logger = $logger;
    }

    /**
     * Sets the specific Pool "namespace" assigned by the cache builder.
     *
     * Using this function developers can segment cache items.
     *
     * @param  string $namespace The pool namespace.
     * @throws InvalidArgumentException If the namespaces is invalid.
     * @return void
     */
    private function setNamespace($namespace)
    {
        if (!ctype_alnum($namespace)) {
            throw new InvalidArgumentException(
                'Namespace must be alphanumeric'
            );
        }

        $this->namespace = $namespace;
    }

    /**
     * Sets the specific Pool class generated by the cache builder.
     *
     * Using this function developers can have the builder generate custom Pool objects.
     *
     * @param  string $class The pool class name.
     * @throws InvalidArgumentException When passed an invalid or nonexistant class.
     * @return void
     */
    private function setPoolClass($class)
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(
                sprintf('Pool class %s does not exist', $class)
            );
        }

        $interfaces = class_implements($class, true);

        if (!in_array(PoolInterface::class, $interfaces)) {
            throw new InvalidArgumentException(sprintf(
                'Pool class %s must inherit from %s',
                $class,
                PoolInterface::class
            ));
        }

        $this->poolClass = $class;
    }

    /**
     * Changes the specific Item class generated by the Pool objects.
     *
     * Using this function developers can have the pool class generate custom Item objects.
     *
     * @param  string $class The item class name.
     * @throws InvalidArgumentException When passed an invalid or nonexistant class.
     * @return void
     */
    private function setItemClass($class)
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(
                sprintf('Item class %s does not exist', $class)
            );
        }

        $interfaces = class_implements($class, true);

        if (!in_array(ItemInterface::class, $interfaces)) {
            throw new InvalidArgumentException(sprintf(
                'Item class %s must inherit from %s',
                $class,
                ItemInterface::class
            ));
        }

        $this->itemClass = $class;
    }

    /**
     * Determine if the variable is an iterable value.
     *
     * @param  mixed $var The value to check
     * @return boolean TRUE if $var is iterable, FALSE otherwise.
     */
    private function isIterable($var)
    {
        return is_array($var) || ($var instanceof Traversable);
    }

    /**
     * Determine if the variable is array accessible.
     *
     * @param  mixed $var The value to check
     * @return boolean TRUE if $var is an array or accessible like an array, FALSE otherwise.
     */
    private function isAccessible($var)
    {
        return is_array($var) || ($var instanceof ArrayAccess);
    }
}
