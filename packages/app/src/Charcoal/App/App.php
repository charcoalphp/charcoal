<?php

namespace Charcoal\App;

use LogicException;
use RuntimeException;
use Dotenv\Dotenv;
// From Slim
use Slim\App as SlimApp;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From PSR-11
use Psr\Container\ContainerInterface;
// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;
// From 'charcoal-app'
use Charcoal\App\Route\RouteManager;
use Error;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler;
use Slim\Routing\Route;

/**
 * Charcoal App
 *
 * This is the primary class with which you instantiate, configure,
 * and run a Slim Framework application within Charcoal.
 */
class App extends SlimApp implements
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
     * Store the unique instance
     *
     * @var App $instance
     */
    protected static $instance;

    /**
     * @var RouteManager
     */
    private $routeManager;

    /**
     * Getter for creating/returning the unique instance of this class.
     *
     * @param ContainerInterface $container The application's container.
     * @return self
     */
    public static function instance(?ContainerInterface $container = null)
    {
        if (!isset(static::$instance)) {
            if (empty($container)) {
                throw new Error('Missing container when calling for App::instance()');
            }
            AppFactory::setContainer($container);

            $app = new static($container);

            // Register routing middleware
            $app->addRoutingMiddleware();

            $logger = ($app->getContainer()->get('logger') ?? null);

            // Add Error middleware + renderers
            $errorMiddleware = $app->addErrorMiddleware(
                ($container->get('config')['debug'] ?? false),
                true,
                true,
                $logger
            );

            $errorMiddleware->setDefaultErrorHandler($container->get('errorHandler'));

            $errorMiddleware->setErrorHandler(
                HttpNotFoundException::class,
                $container->get('notFoundHandler')
            );

            $errorMiddleware->setErrorHandler(
                HttpMethodNotAllowedException::class,
                $container->get('notAllowedHandler')
            );

            static::$instance = $app;
        }
        return static::$instance;
    }

    /**
     * Create new Charcoal application (and SlimApp).
     *
     * @param ContainerInterface $container The application's container.
     * @throws LogicException If trying to create a new instance of a singleton.
     */
    public function __construct(ContainerInterface $container)
    {
        if (isset(static::$instance)) {
            throw new LogicException(
                sprintf('Cannot create a new instance of singleton %s', static::class)
            );
        }
        $responseFactory = new Psr17Factory();
        parent::__construct($responseFactory, $container);
    }

    /**
     * Run application.
     *
     * Initialize the Charcoal application before running (with SlimApp).
     *
     * @uses   self::setup()
     * @param  ServerRequestInterface|null $request
     * @param  ResponseInterface|null $response
     * @return void
     */
    public function run(?ServerRequestInterface $request = null, ?ResponseInterface $response = null): void
    {
        $this->setup();
        parent::run($request, $response);
    }

    /**
     * Registers the default services and features that Charcoal needs to work.
     *
     * @return void
     */
    private function setup()
    {
        $config = $this->config();

        if (!empty($config['timezone'])) {
            date_default_timezone_set($config['timezone']);
        }

        // Setup env
        $dotenv = Dotenv::createImmutable($config['basePath']);
        $dotenv->safeLoad();

        // Setup routes
        $this->routeManager()->setupRoutes();

        // Setup modules
        $this->setupModules();

        // Setup routable (if enabled or not running CLI mode)
        if (PHP_SAPI !== 'cli' && $config['routables'] !== false) {
            $this->setupRoutables();
        }

        // Setup middlewares
        $this->setupMiddlewares();
    }

    /**
     * Retrieve (create, if necessary) the application's route manager.
     *
     * @return RouteManager
     */
    private function routeManager()
    {
        if (!isset($this->routeManager)) {
            $config = $this->config();
            $routesConfig = (isset($config['routes']) ? $config['routes'] : [] );

            $this->routeManager = new RouteManager([
                'config' => $routesConfig,
                'app'    => $this
            ]);
        }

        return $this->routeManager;
    }

    /**
     * @return void
     */
    private function setupModules()
    {
        $container = $this->getContainer();
        $modules = $container->get('config')['modules'];
        foreach ($modules as $moduleIdent => $moduleConfig) {
            $module = $container->get('module/factory')->create($moduleIdent);
            $module->setup();
        }
    }

    /**
     * Setup the application's "global" routables.
     *
     * Routables can only be defined globally (app-level) for now.
     *
     * @return void
     */
    private function setupRoutables()
    {
        $app = $this;

        // For now, need to rely on a catch-all...
        $this->get(
            '{catchall:.*}',
            function (
                ServerRequestInterface $request,
                ResponseInterface $response,
                array $args
            ) use ($app) {
                $config    = $app->config();
                $routables = $config['routables'];

                if (is_array($routables) && !empty($routables)) {
                    $routeFactory = $app->getContainer()->get('route/factory');
                    foreach ($routables as $routableType => $routableOptions) {
                        $route = $routeFactory->create($routableType, [
                            'path'      => $args['catchall'],
                            'config'    => $routableOptions,
                            'container' => $app->getContainer(),
                        ]);
                        if ($route->pathResolvable($this)) {
                            $app->getContainer()->get('logger')->debug(
                                sprintf('Loaded routable "%s" for path %s', $routableType, $args['catchall'])
                            );
                            $routeResponse = $route($request, $response);
                            if ($routeResponse instanceof \Psr\Http\Message\ResponseInterface) {
                                return $routeResponse;
                            }
                        }
                    }
                }

                // If this point is reached, no routable has provided a callback. 404.
                throw new \Slim\Exception\HttpNotFoundException($request);
            }
        );
    }

    /**
     * @throws RuntimeException If the middleware was not set properly on the container.
     * @return void
     */
    private function setupMiddlewares()
    {
        $container = $this->getContainer();
        $middlewaresConfig = $container->get('config')['middlewares'];
        if (!$middlewaresConfig) {
            return;
        }

        foreach ($middlewaresConfig as $id => $opts) {
            if (isset($opts['active']) && $opts['active'] === true) {
                if ($id === 'charcoal/app/middleware/cache') {
                    $id = 'charcoal/cache/middleware/cache';

                    $container->get('logger')->warning(sprintf(
                        'Middleware "%1$s" is deprecated since %3$s. Use "%2$s" instead.',
                        'charcoal/app/middleware/cache',
                        'charcoal/cache/middleware/cache',
                        '0.8.0'
                    ));
                }

                if (!($container->has('middlewares/' . $id))) {
                    throw new RuntimeException(
                        sprintf('Middleware "%s" is not set on container.', $id)
                    );
                }

                $this->add($container->get('middlewares/' . $id));
            }
        }
    }

    /**
     * @throws LogicException If trying to clone an instance of a singleton.
     * @return void
     */
    final public function __clone()
    {
        throw new LogicException(
            sprintf(
                'Cloning "%s" is not allowed.',
                get_called_class()
            )
        );
    }

    /**
     * @throws LogicException If trying to unserialize an instance of a singleton.
     * @return void
     */
    final public function __wakeup()
    {
        throw new LogicException(
            sprintf(
                'Unserializing "%s" is not allowed.',
                get_called_class()
            )
        );
    }
}
