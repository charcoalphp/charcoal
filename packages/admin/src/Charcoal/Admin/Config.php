<?php

namespace Charcoal\Admin;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Config\AbstractConfig;
// From 'charcoal-app'
use Charcoal\App\Handler\HandlerConfig;
use Charcoal\App\Route\RouteConfig;

/**
 * Admin Config.
 */
class Config extends AbstractConfig
{
    public const DEFAULT_BASE_PATH = 'admin';

    /**
     * The base path for the admin module's route group.
     */
    private string $basePath = self::DEFAULT_BASE_PATH;

    /**
     * @var array
     */
    public $routes = [];

    private array $handlers = [];

    /**
     * @var array
     */
    public $acl = [];

    /**
     * The application's view/rendering configset.
     *
     * @var array
     */
    protected $view;

    /**
     * The default data is defined in a JSON file.
     *
     * @return array
     */
    #[\Override]
    public function defaults()
    {
        $baseDir = rtrim(realpath(__DIR__ . '/../../../'), '/');
        $confDir = $baseDir . '/config';

        return $this->loadFile($confDir . '/admin.config.default.json');
    }

    /**
     * Set the admin module's route group.
     *
     * @param  string $path The admin module base path.
     * @throws InvalidArgumentException If the route group is invalid.
     */
    public function setBasePath($path): static
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path must be a string'
            );
        }

        // Can not be empty
        if ($path === '') {
            throw new InvalidArgumentException(
                'Path can not be empty'
            );
        }

        $this->basePath = $path;
        return $this;
    }

    /**
     * Retrieve the admin module's route group.
     */
    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * Parse the admin module's route configuration.
     *
     * @see    \Charcoal\App\AppConfig::setRoutes() For a similar implementation.
     * @param  array $routes The route configuration structure to set.
     */
    public function setRoutes(array $routes): static
    {
        $toIterate = RouteConfig::defaultRouteTypes();
        foreach ($routes as $key => $val) {
            if (in_array($key, $toIterate) && isset($this->routes[$key])) {
                $this->routes[$key] = array_merge($this->routes[$key], $val);
            } else {
                $this->routes[$key] = $val;
            }
        }

        return $this;
    }

    /**
     * Define custom response and error handlers.
     *
     * Charcoal overrides four of Slim's standard handlers:
     *
     * - "notFoundHandler"
     * - "notAllowedHandler"
     * - "errorHandler"
     * - "phpErrorHandler"
     *
     * @param  array $handlers The handlers configuration structure to set.
     */
    public function setHandlers(array $handlers): static
    {
        $this->handlers = array_fill_keys(HandlerConfig::defaultHandlerTypes(), []);
        $this->handlers['defaults'] = [];

        foreach ($handlers as $handler => $data) {
            $this->handlers[$handler] = array_replace(
                $this->handlers[$handler],
                $data
            );
        }

        return $this;
    }

    public function handlers(): array
    {
        return $this->handlers;
    }

    /**
     * Configure the application's global view service.
     *
     * @param  array $view The global configset for the application's view service.
     * @throws InvalidArgumentException If the argument is not a configset.
     */
    public function setView(array $view): static
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Retrieve the configset for application's global view service.
     *
     * @return array
     */
    public function view()
    {
        return $this->view;
    }
}
