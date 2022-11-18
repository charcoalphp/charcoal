<?php

namespace Charcoal\App;

use Exception;
use InvalidArgumentException;
use UnexpectedValueException;
// From PSR-7
use Psr\Http\Message\UriInterface;
// From Slim
use Slim\Http\Uri;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Charcoal App configuration
 */
class AppConfig extends AbstractConfig
{
    /**
     * The application's timezone.
     */
    private ?string $timezone;

    /**
     * The application's name.
     *
     * For internal usage.
     */
    private ?string $projectName;

    /**
     * The base URL (public) for the Charcoal installation.
     */
    private ?UriInterface $baseUrl = null;

    /**
     * The base path for the Charcoal installation.
     */
    private ?string $basePath = null;

    /**
     * The path to the public / web directory.
     *
     */
    private ?string $publicPath = null;

    /**
     * The path to the cache directory.
     */
    private ?string $cachePath = null;

    /**
     * The path to the logs directory.
     */
    private ?string $logsPath = null;

    /**
     * Whether the debug mode is enabled (TRUE) or not (FALSE).
     */
    private bool $devMode = false;

    /**
     * The application's routes.
     */
    private array $routes = [];

    /**
     * The application's dynamic routes.
     *
     * @var array|boolean
     */
    private $routables = [];

    /**
     * The application's HTTP middleware.
     */
    private array $middlewares = [];

    /**
     * The application's handlers.
     */
    private array $handlers = [];

    /**
     * The application's modules.
     */
    private array $modules = [];

    /**
     * The application's API credentials and service configsets.
     */
    private array $apis = [];

    /**
     * The application's caching configset.
     */
    private array $cache = [];

    /**
     * The application's logging configset.
     *
     * @var array
     */
    private array $logger = [];

    /**
     * The application's view/rendering configset.
     */
    protected array $view = [];

    /**
     * The application's database configsets.
     */
    private array $databases = [];

    /**
     * The application's default database configset.
     */
    private ?string $defaultDatabase;

    /**
     * The application's filesystem configset.
     */
    private array $filesystem = [];

    /**
     * Default app-config values.
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'project_name'     => '',
            'timezone'         => 'UTC',
            'routes'           => [],
            'routables'        => [],
            'middlewares'      => [],
            'handlers'         => [],
            'modules'          => [],
            'cache'            => [],
            'logger'           => [],
            'view'             => [],
            'databases'        => [],
            'default_database' => 'default',
            'dev_mode'         => false,
        ];
    }

    /**
     * @param array $values Array of values to resolve.
     * @return array
     */
    public function resolveValues(array $values): array
    {
        return array_map([$this, 'resolveValue'], $values);
    }

    /**
     * Replaces placeholders (%app.key%) by their values in the config.
     *
     * @param  mixed $value A value to resolve.
     * @throws UnexpectedValueException If the resolved value is not a string or number.
     * @return mixed
     */
    public function resolveValue($value)
    {
        $tags = [
            'app.base_path'   => $this->basePath(),
            'app.public_path' => $this->publicPath(),
            'app.cache_path'  => $this->cachePath(),
            'app.logs_path'   => $this->logsPath(),
            'packages.path'   => ($_ENV['PACKAGES_PATH'] ?? 'vendor/charcoal'),
        ];

        if (is_string($value)) {
            return preg_replace_callback('/%%|%([^%\s]+)%/', function ($match) use ($tags, $value) {
                // skip escaped %%
                if (!isset($match[1])) {
                    return '%%';
                }

                $tag = $match[1];

                $resolved = ($tags[$tag] ?? null);

                if (!is_string($resolved) && !is_numeric($resolved)) {
                    $resolvedType = (is_object($resolved) ? get_class($resolved) : gettype($resolved));

                    throw new UnexpectedValueException(sprintf(
                        'Invalid config parameter "%s" inside string value "%s";' .
                        ' must be a string or number, received %s',
                        $tag,
                        $value,
                        $resolvedType
                    ));
                }

                return $resolved;
            }, $value);
        }

        return $value;
    }

    /**
     * Adds a configuration file to the configset.
     *
     * Natively supported file formats: INI, JSON, PHP.
     *
     * @uses   FileAwareTrait::loadFile()
     * @param  string $path The file to load and add.
     * @return self
     */
    public function addFile(string $path): self
    {
        $path = $this->resolveValue($path);

        return parent::addFile($path);
    }

    /**
     * Set the application's absolute root path.
     *
     * Resolves symlinks with realpath() and ensure trailing slash.
     *
     * @param  string|null $path The absolute path to the application's root directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setBasePath(?string $path): self
    {
        if ($path === null) {
            throw new InvalidArgumentException(
                'The base path is required.'
            );
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The base path must be a string'
            );
        }

        $this->basePath = rtrim(realpath($path), '\\/');
        return $this;
    }

    /**
     * Retrieve the application's absolute root path.
     *
     * @return string|null The absolute path to the application's root directory.
     */
    public function basePath(): ?string
    {
        return $this->basePath;
    }

    /**
     * Set the application's absolute path to the public web directory.
     *
     * @param  string|null $path The path to the application's public directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setPublicPath(?string $path): self
    {
        if ($path === null) {
            $this->publicPath = null;
            return $this;
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The public path must be a string'
            );
        }

        $this->publicPath = rtrim(realpath($path), '\\/');
        return $this;
    }

    /**
     * Retrieve the application's absolute path to the public web directory.
     *
     * @return string The absolute path to the application's public directory.
     */
    public function publicPath(): string
    {
        if ($this->publicPath === null) {
            $this->publicPath = $this->basePath() . DIRECTORY_SEPARATOR . 'www';
        }

        return $this->publicPath;
    }

    /**
     * Set the application's absolute path to the cache directory.
     *
     * @param  string|null $path The path to the application's cache directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setCachePath(?string $path): self
    {
        if ($path === null) {
            $this->cachePath = null;
            return $this;
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The cache path must be a string'
            );
        }

        $this->cachePath = rtrim(realpath($path), '\\/');
        return $this;
    }

    /**
     * Retrieve the application's absolute path to the cache directory.
     *
     * @return string The absolute path to the application's cache directory.
     */
    public function cachePath(): string
    {
        if ($this->cachePath === null) {
            $this->cachePath = $this->basePath() . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache';
        }

        return $this->cachePath;
    }

    /**
     * Set the application's absolute path to the logs directory.
     *
     * @param  string|null $path The path to the application's logs directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setLogsPath(?string $path): self
    {
        if ($path === null) {
            $this->logsPath = null;
            return $this;
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The logs path must be a string'
            );
        }

        $this->logsPath = rtrim(realpath($path), '\\/');
        return $this;
    }

    /**
     * Retrieve the application's absolute path to the logs directory.
     *
     * @return string The absolute path to the application's logs directory.
     */
    public function logsPath(): string
    {
        if ($this->logsPath === null) {
            $this->logsPath = $this->basePath() . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'logs';
        }

        return $this->logsPath;
    }

    /**
     * Set the application's fully qualified base URL to the public web directory.
     *
     * @param  UriInterface|string $uri The base URI to the application's web directory.
     * @return self
     */
    public function setBaseUrl($uri): self
    {
        if (is_string($uri)) {
            $this->baseUrl = Uri::createFromString($uri);
        } else {
            $this->baseUrl = $uri;
        }
        return $this;
    }

    /**
     * Retrieve the application's fully qualified base URL to the public web directory.
     *
     * @return UriInterface|null The base URI to the application's web directory.
     */
    public function baseUrl(): ?UriInterface
    {
        return $this->baseUrl;
    }

    /**
     * Set the application's default timezone.
     *
     * @param  string $timezone The timezone string.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Retrieve the application's default timezone.
     *
     * Will be used by the PHP date and date-time functions.
     *
     * @return string
     */
    public function timezone(): string
    {
        return ($this->timezone ?? 'UTC');
    }

    /**
     * Sets the project name.
     *
     * @param string|null $projectName The project name.
     * @throws InvalidArgumentException If the project argument is not a string (or null).
     * @return self
     */
    public function setProjectName(?string $projectName): self
    {
        if ($projectName === null) {
            $this->projectName = null;
            return $this;
        }
        if (!is_string($projectName)) {
            throw new InvalidArgumentException(
                'Project name must be a string'
            );
        }

        $this->projectName = $projectName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function projectName(): ?string
    {
        if ($this->projectName === null) {
            $baseUrl = $this->baseUrl();
            if ($baseUrl) {
                return $baseUrl->getHost();
            }
        }

        return $this->projectName;
    }

    /**
     * @param boolean $devMode The "dev mode" flag.
     * @return self
     */
    public function setDevMode(bool $devMode): self
    {
        $this->devMode = !!$devMode;
        return $this;
    }

    /**
     * @return boolean
     */
    public function devMode(): bool
    {
        return !!$this->devMode;
    }

    /**
     * Configure the application's global view service.
     *
     * @param  array $view The global configset for the application's view service.
     * @throws InvalidArgumentException If the argument is not a configset.
     * @return self
     */
    public function setView(array $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Retrieve the configset for application's global view service.
     *
     * @return array
     */
    public function view(): array
    {
        return $this->view;
    }

    /**
     * Parse the application's API configuration.
     *
     * @param  array $apis The API configuration structure to set.
     * @return self
     */
    public function setApis(array $apis): self
    {
        $this->apis = $apis;
        return $this;
    }

    /**
     * @return array
     */
    public function apis(): array
    {
        return $this->apis;
    }

    /**
     * Parse the application's route configuration.
     *
     * @see    \Charcoal\Admin\Config::setRoutes() For a similar implementation.
     * @param  array $routes The route configuration structure to set.
     * @return self
     */
    public function setRoutes(array $routes): self
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @return array
     */
    public function routes(): array
    {
        return $this->routes;
    }

    /**
     * @param  array|boolean $routables The routable configuration structure to set or FALSE to disable dynamic routing.
     * @return self
     */
    public function setRoutables($routables): self
    {
        if ($routables !== false) {
            if (!is_array($routables) || empty($routables)) {
                $routables = [];
            }
        }

        $this->routables = $routables;
        return $this;
    }

    /**
     * @return array|boolean
     */
    public function routables()
    {
        return $this->routables;
    }

    /**
     * Parse the application's HTTP middleware.
     *
     * @param  array $middlewares The middleware configuration structure to set.
     * @return self
     */
    public function setMiddlewares(array $middlewares): self
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * @return array
     */
    public function middlewares(): array
    {
        return $this->middlewares;
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
     * @return self
     */
    public function setHandlers(array $handlers): self
    {
        $this->handlers = $handlers;
        return $this;
    }

    /**
     * @return array
     */
    public function handlers(): array
    {
        return $this->handlers;
    }

    /**
     * Set the configuration modules.
     *
     * @param array $modules The module configuration structure to set.
     * @return self
     */
    public function setModules(array $modules): self
    {
        $this->modules = $modules;
        return $this;
    }

    /**
     * @return array
     */
    public function modules(): array
    {
        return $this->modules;
    }

    /**
     * Configure the application's global cache service.
     *
     * @param  array $cache The global config for the application's cache service.
     * @throws InvalidArgumentException If the argument is not a configset.
     * @return self
     */
    public function setCache(array $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Retrieve the configset for application's global cache service.
     *
     * @return array
     */
    public function cache(): array
    {
        return $this->cache;
    }

    /**
     * Configure the application's global logger service.
     *
     * @param  array $logger The global config for the application's logger service.
     * @throws InvalidArgumentException If the argument is not a configset.
     * @return self
     */
    public function setLogger(array $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Retrieve the configset for application's global logger service.
     *
     * @return array
     */
    public function logger(): array
    {
        return $this->logger;
    }

    /**
     * @param array $databases The available databases config.
     * @return self
     */
    public function setDatabases(array $databases): self
    {
        $this->databases = $databases;
        return $this;
    }

    /**
     * @throws Exception If trying to access this method and no databases were set.
     * @return array
     */
    public function databases(): array
    {
        if ($this->databases === null) {
            throw new Exception(
                'Invalid app config: Databases are not set.'
            );
        }
        return $this->databases;
    }

    /**
     * @param string $ident The ident of the database to return the configuration of.
     * @throws InvalidArgumentException If the ident argument is not a string.
     * @throws Exception If trying to access an invalid database.
     * @return array
     */
    public function databaseConfig(string $ident): array
    {
        $databases = $this->databases();
        if (!isset($databases[$ident])) {
            throw new Exception(
                sprintf('Invalid app config: no database configuration matches "%s".', $ident)
            );
        }
        return $databases[$ident];
    }

    /**
     * @param string $defaultDatabase The default database ident.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setDefaultDatabase(string $defaultDatabase): self
    {
        $this->defaultDatabase = $defaultDatabase;
        return $this;
    }

    /**
     * @param string $ident  The database ident.
     * @param array  $config The database options.
     * @throws InvalidArgumentException If the arguments are invalid.
     * @return self
     */
    public function addDatabase(string $ident, array $config): self
    {
        if ($this->databases === null) {
            $this->databases = [];
        }
        $this->databases[$ident] = $config;
        return $this;
    }

    /**
     * @throws Exception If trying to access this method before a setter.
     * @return string
     */
    public function defaultDatabase(): string
    {
        if ($this->defaultDatabase === null) {
            throw new Exception(
                'Invalid app config: default database is not set.'
            );
        }
        return $this->defaultDatabase;
    }

    /**
     * Configure the application's global file system.
     *
     * @param  array $filesystem The global config for the application's file system.
     * @throws InvalidArgumentException If the argument is not a configset.
     * @return self
     */
    public function setFilesystem(array $filesystem): self
    {
        $this->filesystem = $filesystem;
        return $this;
    }

    /**
     * Retrieve the configset for application's global file system.
     *
     * @return array
     */
    public function filesystem(): array
    {
        return $this->filesystem;
    }
}
