<?php

namespace Charcoal\App\Facade;

use Pimple\Container;
use RuntimeException;

/**
 * Facade
 *
 * The facade class acts as a shortcut to a container service.
 */
abstract class Facade
{
    protected static Container $resolver;

    /**
     * @var array<string, object>
     */
    protected static array $resolvedInstances = [];

    public static function setFacadeResolver(Container $resolver): void
    {
        static::$resolver = $resolver;
    }

    /**
     * @return object
     */
    public static function getFacadeInstance(): object
    {
        return static::resolveFacadeInstance(static::getFacadeName());
    }

    /**
     * Get the container service key the facade is providing alias for.
     */
    protected static function getFacadeName(): string
    {
        throw new RuntimeException(sprintf(
            'The facade [%s] does not provide a container service key.',
            get_called_class()
        ));
    }

    protected static function resolveFacadeInstance(string $key): object
    {
        if (isset(static::$resolvedInstances[$key])) {
            return static::$resolvedInstances[$key];
        }

        $instance = static::$resolver[$key];
        if (!is_object($instance)) {
            throw new RuntimeException(sprintf(
                'The facade [%s] instance must be an object, received %s',
                get_called_class(),
                gettype($instance)
            ));
        }

        static::$resolvedInstances[$key] = $instance;
        return static::$resolvedInstances[$key];
    }

    public static function clearResolvedFacadeInstance(string $key): void
    {
        unset(static::$resolvedInstances[$key]);
    }

    public static function clearResolvedFacadeInstances(): void
    {
        static::$resolvedInstances = [];
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  mixed[] $args
     * @return mixed
     */
    public static function __callStatic(string $method, array $args = [])
    {
        return static::getFacadeInstance()->$method(...$args);
    }
}
