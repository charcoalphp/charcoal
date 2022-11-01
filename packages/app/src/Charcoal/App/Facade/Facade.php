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
     * Get the container key the facade is providing alias for.
     *
     * @return string
     */
    protected static function getContainerKey(): string
    {
        throw new RuntimeException(sprintf('The facade [%s] is not providing a container key.', get_called_class()));
    }

    /**
     * @param $key
     * @return mixed
     */
    protected static function resolveContainerService($key)
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

        if (! $instance) {
            throw new RuntimeException(sprintf('The facade [%s]\'s root is not set.', get_called_class()));
        }

        return $instance->$method(...$args);
    }
}
