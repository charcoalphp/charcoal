<?php

namespace Charcoal\App\Facade;

use Charcoal\App\App;
use RuntimeException;

/**
 * Facade
 *
 * The facade class acts as a shortcut to a container service.
 */
class Facade
{
    protected static App $app;
    /**
     * @var array<string, object>
     */
    protected static array $resolvedServices = [];

    public static function setApp(App $app)
    {
        static::$app = $app;
    }

    /**
     * @return mixed
     */
    public static function getRoot()
    {
        return static::resolveContainerService(static::getContainerKey());
    }

    /**
     * Get the container service key the facade is providing alias for.
     */
    protected static function getContainerServiceKey(): string
    {
        throw new RuntimeException(sprintf('The facade [%s] does not provide a container service key.', get_called_class()));
    }

    protected static function resolveContainerService(string $key): object
    {
        if (isset(static::$resolvedServices[$key])) {
            return static::$resolvedServices[$key];
        }

        static::$resolvedServices[$key] = static::$app->getContainer()[$key];
        return static::$resolvedServices[$key];
    }

    public static function clearResolvedContainerService(string $key)
    {
        unset(static::$resolvedServices[$key]);
    }

    public static function clearResolvedContainerServices()
    {
        static::$resolvedServices = [];
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
        $instance = static::getRoot();

        if (!$instance) {
            throw new RuntimeException(
                sprintf('The facade [%s] root is not defined', get_called_class())
            );
        }

        return $instance->$method(...$args);
    }
}
