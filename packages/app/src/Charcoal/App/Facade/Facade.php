<?php

namespace Charcoal\App\Facade;

use Charcoal\App\App;
use RuntimeException;

/**
 * Facade
 *
 * The facade class acts as a shortcut to a container service.
 */
abstract class Facade
{
    protected static App $app;
    /**
     * @var array<string, object>
     */
    protected static array $resolvedInstances = [];

    public static function setFacadeApp(App $app)
    {
        static::$app = $app;
    }

    /**
     * @return mixed
     */
    public static function getFacadeInstance()
    {
        return static::resolveFacadeInstance(static::getFacadeName());
    }

    /**
     * Get the container service key the facade is providing alias for.
     */
    protected static function getFacadeName(): string
    {
        throw new RuntimeException(
            sprintf('The facade [%s] does not provide a container service key.', get_called_class())
        );
    }

    protected static function resolveFacadeInstance(string $key): object
    {
        if (isset(static::$resolvedInstances[$key])) {
            return static::$resolvedInstances[$key];
        }

        static::$resolvedInstances[$key] = static::$app->getContainer()[$key];
        return static::$resolvedInstances[$key];
    }

    public static function clearResolvedFacadeInstance(string $key)
    {
        unset(static::$resolvedInstances[$key]);
    }

    public static function clearResolvedFacadeInstances()
    {
        static::$resolvedInstances = [];
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param  array $args
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $args = [])
    {
        $instance = static::getFacadeInstance();

        if (!$instance) {
            throw new RuntimeException(
                sprintf('The facade [%s] root is not defined', get_called_class())
            );
        }

        return $instance->$method(...$args);
    }
}
