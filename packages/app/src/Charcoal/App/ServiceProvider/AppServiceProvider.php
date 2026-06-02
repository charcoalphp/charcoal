<?php

namespace Charcoal\App\ServiceProvider;

// From PSR-7
use Charcoal\Factory\GenericResolver;
use Psr\Http\Message\UriInterface;
// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;
// From Slim
use Slim\Http\Uri;
// From 'league/climate'
use League\CLImate\CLImate;
// From Mustache
use Mustache\LambdaHelper as LambdaHelper;
use Charcoal\Factory\GenericFactory as Factory;
use Charcoal\Cache\ServiceProvider\CacheServiceProvider;
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Charcoal\App\AppConfig;
use Charcoal\App\Action\ActionInterface;
use Charcoal\App\Handler\Error;
use Charcoal\App\Handler\Maintenance;
use Charcoal\App\Handler\NotAllowed;
use Charcoal\App\Handler\NotFound;
use Charcoal\App\Handler\PhpError;
use Charcoal\App\Middleware\IpMiddleware;
use Charcoal\App\Module\ModuleInterface;
use Charcoal\App\Route\ActionRoute;
use Charcoal\App\Route\RouteInterface;
use Charcoal\App\Route\TemplateRoute;
use Charcoal\App\Script\ScriptInterface;
use Charcoal\App\ServiceProvider\DatabaseServiceProvider;
use Charcoal\App\ServiceProvider\FilesystemServiceProvider;
use Charcoal\App\ServiceProvider\ScriptServiceProvider;
use Charcoal\App\ServiceProvider\LoggerServiceProvider;
use Charcoal\App\Template\TemplateInterface;
use Charcoal\App\Template\TemplateBuilder;
use Charcoal\App\Template\WidgetInterface;
use Charcoal\App\Template\WidgetBuilder;
use Charcoal\View\Twig\DebugHelpers as TwigDebugHelpers;
use Charcoal\View\Twig\HelpersInterface as TwigHelpersInterface;
use Charcoal\View\Twig\UrlHelpers as TwigUrlHelpers;
use Charcoal\View\ViewServiceProvider;

/**
 * Application Service Provider
 *
 * Configures Charcoal and Slim and provides various Charcoal services to a container.
 *
 * ## Services
 * - `logger` `\Psr\Log\Logger`
 *
 * ## Helpers
 * - `logger/config` `\Charcoal\App\Config\LoggerConfig`
 *
 * ## Requirements / Dependencies
 * - `config` A `ConfigInterface` must have been previously registered on the container.
 */
class AppServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param  Container $container A service container.
     */
    public function register(Container $container): void
    {
        $container->register(new CacheServiceProvider());
        $container->register(new DatabaseServiceProvider());
        $container->register(new FilesystemServiceProvider());
        $container->register(new LoggerServiceProvider());
        $container->register(new ScriptServiceProvider());
        $container->register(new TranslatorServiceProvider());
        $container->register(new ViewServiceProvider());

        $this->registerKernelServices($container);
        $this->registerHandlerServices($container);
        $this->registerRouteServices($container);
        $this->registerMiddlewareServices($container);
        $this->registerRequestControllerServices($container);
        $this->registerModuleServices($container);
        $this->registerViewServices($container);
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerKernelServices(Container $container)
    {
        if (!isset($container['config'])) {
            $container['config'] = new AppConfig();
        }

        if (!isset($container['debug'])) {
            /**
             * Application Debug Mode
             *
             * @param  Container $container A service container.
             * @return boolean
             */
            $container['debug'] = function (Container $container): bool {
                if (isset($container['config']['debug'])) {
                    return (bool)$container['config']['debug'];
                }

                if (isset($container['config']['dev_mode'])) {
                    return (bool)$container['config']['dev_mode'];
                }

                return false;
            };
        }

        if (!isset($container['base-url'])) {
            /**
             * Base URL as a PSR-7 UriInterface object for the current request
             * or the Charcoal application.
             *
             * @param  Container $container A service container.
             * @return \Psr\Http\Message\UriInterface
             */
            $container['base-url'] = function (Container $container) {
                if (isset($container['config']['base_url'])) {
                    $baseUrl = $container['config']['base_url'];
                } else {
                    $baseUrl = $container['request']->getUri()->getBaseUrl();
                }

                $baseUrl = Uri::createFromString($baseUrl)->withUserInfo('');

                /** Fix the base path */
                $path = $baseUrl->getPath();
                if ($path) {
                    $baseUrl = $baseUrl->withBasePath($path)->withPath('');
                }

                return $baseUrl;
            };
        }
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerHandlerServices(Container $container)
    {
        $container['phpErrorHandler/class'] = PhpError::class;
        $container['errorHandler/class'] = Error::class;
        $container['notFoundHandler/class'] = NotFound::class;
        $container['notAllowedHandler/class'] = NotAllowed::class;
        $container['maintenanceHandler/class'] = Maintenance::class;

        $handlersConfig = $container['config']['handlers'];

        if (isset($container['notFoundHandler'])) {
            /**
             * HTTP 404 (Not Found) handler.
             *
             * @param  object|\Charcoal\App\Handler\HandlerInterface $handler   An error handler instance.
             * @param  Container                                     $container A container instance.
             * @return \Charcoal\App\Handler\HandlerInterface
             */
            $container->extend('notFoundHandler', function ($handler, Container $container) use ($handlersConfig) {
                if ($handler instanceof \Slim\Handlers\NotFound) {
                    $config  = ($handlersConfig['notFound'] ?? []);
                    $class   = $container['notFoundHandler/class'];
                    $handler = new $class($container, $config);
                    $handler->init();
                }

                return $handler;
            });
        }

        if (isset($container['notAllowedHandler'])) {
            /**
             * HTTP 405 (Not Allowed) handler.
             *
             * @param  object|\Charcoal\App\Handler\HandlerInterface $handler   An error handler instance.
             * @param  Container                                     $container A container instance.
             * @return \Charcoal\App\Handler\HandlerInterface
             */
            $container->extend('notAllowedHandler', function ($handler, Container $container) use ($handlersConfig) {
                if ($handler instanceof \Slim\Handlers\NotAllowed) {
                    $config  = ($handlersConfig['notAllowed'] ?? []);
                    $class   = $container['notAllowedHandler/class'];
                    $handler = new $class($container, $config);
                    $handler->init();
                }

                return $handler;
            });
        }

        if (isset($container['phpErrorHandler'])) {
            /**
             * HTTP 500 (Error) handler for PHP 7+ Throwables.
             *
             * @param  object|\Charcoal\App\Handler\HandlerInterface $handler   An error handler instance.
             * @param  Container                                     $container A container instance.
             * @return \Charcoal\App\Handler\HandlerInterface
             */
            $container->extend('phpErrorHandler', function ($handler, Container $container) use ($handlersConfig) {
                if ($handler instanceof \Slim\Handlers\PhpError) {
                    $config  = ($handlersConfig['phpError'] ?? []);
                    $class   = $container['phpErrorHandler/class'];
                    $handler = new $class($container, $config);
                    $handler->init();
                }

                return $handler;
            });
        }

        if (isset($container['errorHandler'])) {
            /**
             * HTTP 500 (Error) handler.
             *
             * @param  object|\Charcoal\App\Handler\HandlerInterface $handler   An error handler instance.
             * @param  Container                                     $container A container instance.
             * @return \Charcoal\App\Handler\HandlerInterface
             */
            $container->extend('errorHandler', function ($handler, Container $container) use ($handlersConfig) {
                if ($handler instanceof \Slim\Handlers\Error) {
                    $config  = ($handlersConfig['error'] ?? []);
                    $class   = $container['errorHandler/class'];
                    $handler = new $class($container, $config);
                    $handler->init();
                }

                return $handler;
            });
        }

        if (!isset($container['maintenanceHandler'])) {
            /**
             * HTTP 503 (Service Unavailable) handler.
             *
             * This handler is not part of Slim.
             *
             * @param  Container $container A service container.
             * @return \Charcoal\App\Handler\HandlerInterface
             */
            $container['maintenanceHandler'] = function (Container $container) use ($handlersConfig) {
                $config  = ($handlersConfig['maintenance'] ?? []);
                $class   = $container['maintenanceHandler/class'];
                $handler = new $class($container, $config);

                return $handler->init();
            };
        }
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerRouteServices(Container $container)
    {
        $container['route/controller/action/class'] = ActionRoute::class;
        $container['route/controller/template/class'] = TemplateRoute::class;

        /**
         * The Route Factory service is used to instanciate new routes.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['route/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'base_class'       => RouteInterface::class,
            'resolver_options' => [
                'suffix' => 'Route',
            ],
            'arguments'  => [
                [
                    'logger' => $container['logger'],
                ],
            ],
        ]));
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerMiddlewareServices(Container $container)
    {
        /**
         * @param  Container $container A service container.
         * @return IpMiddleware
         */
        $container['middlewares/charcoal/app/middleware/ip'] = function (container $container): \Charcoal\App\Middleware\IpMiddleware {
            $wareConfig = $container['config']['middlewares']['charcoal/app/middleware/ip'];
            return new IpMiddleware($wareConfig);
        };
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerRequestControllerServices(Container $container)
    {
        /**
         * The Action Factory service is used to instanciate new actions.
         *
         * - Actions are `ActionInterface` and must be suffixed with `Action`.
         * - The container is passed to the created action constructor, which will call `setDependencies()`.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['action/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'base_class'       => ActionInterface::class,
            'resolver_options' => [
                'suffix' => 'Action',
            ],
            'arguments' => [
                [
                    'container' => $container,
                    'logger'    => $container['logger'],
                ],
            ],
        ]));

        /**
         * The Template Factory service is used to instanciate new templates.
         *
         * - Templates are `TemplateInterface` and must be suffixed with `Template`.
         * - The container is passed to the created template constructor, which will call `setDependencies()`.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['template/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'base_class'       => TemplateInterface::class,
            'resolver_options' => [
                'suffix' => 'Template',
            ],
            'arguments' => [
                [
                    'container' => $container,
                    'logger'    => $container['logger'],
                ],
            ],
        ]));

        /**
         * The Widget Factory service is used to instanciate new widgets.
         *
         * - Widgets are `WidgetInterface` and must be suffixed with `Widget`.
         * - The container is passed to the created widget constructor, which will call `setDependencies()`.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['widget/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'base_class'       => WidgetInterface::class,
            'resolver_options' => [
                'suffix' => 'Widget',
            ],
            'arguments' => [
                [
                    'container' => $container,
                    'logger'    => $container['logger'],
                ],
            ],
        ]));

        /**
         * @param  Container $container A service container.
         * @return WidgetBuilder
         */
        $container['widget/builder'] = (fn(Container $container): \Charcoal\App\Template\WidgetBuilder => new WidgetBuilder($container['widget/factory'], $container));
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerModuleServices(Container $container)
    {
        /**
         * The Module Factory service is used to instanciate new modules.
         *
         * - Modules are `ModuleInterface` and must be suffixed with `Module`.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['module/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'base_class'       => ModuleInterface::class,
            'resolver_options' => [
                'suffix' => 'Module',
            ],
            'arguments'  => [
                [
                    'logger' => $container['logger'],
                ],
            ],
        ]));

        /**
         * The modules as PHP classes.
         *
         * @param  Container $container A service container.
         * @return array
         */
        $container['module/classes'] = function (Container $container): array {
            $appConfig = $container['config'];

            $modules = $appConfig['modules'];
            $modules = array_keys($modules);

            $moduleResolver = new GenericResolver([
                'suffix' => 'Module',
            ]);

            $modules = array_map($moduleResolver->resolve(...), $modules);

            array_filter($modules, class_exists(...));

            return $modules;
        };
    }

    /**
     * Add helpers to the view services.
     *
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerViewServices(Container $container)
    {
        $this->registerMustacheHelpersServices($container);

        $this->registerTwigHelpersServices($container);
    }

    /**
     * @param Container $container The DI container.
     */
    protected function registerMustacheHelpersServices(Container $container): void
    {
        if (!isset($container['view/mustache/helpers'])) {
            $container['view/mustache/helpers'] = (fn(): array => []);
        }

        /**
         * Extend helpers for the Mustache Engine
         *
         * @return array
         */
        $container->extend('view/mustache/helpers', function (array $helpers, Container $container): array {
            $baseUrl = $container['base-url'];
            $urls = [
                /**
                 * Application debug mode.
                 *
                 * @return boolean
                 */
                'debug' => ($container['config']['debug'] ?? false),
                /**
                 * Retrieve the base URI of the project.
                 *
                 * @return UriInterface|null
                 */
                'siteUrl' => $baseUrl,
                /**
                 * Alias of "siteUrl"
                 *
                 * @return UriInterface|null
                 */
                'baseUrl' => $baseUrl,
                /**
                 * Prepend the base URI to the given path.
                 *
                 * @param  string $uri A URI path to wrap.
                 * @return UriInterface|null
                 */
                'withBaseUrl' => function ($uri, ?LambdaHelper $helper = null) use ($baseUrl) {
                    if ($helper instanceof LambdaHelper) {
                        $uri = $helper->render($uri);
                    }
                    $uri = strval($uri);
                    if ($uri === '') {
                        $uri = $baseUrl->withPath('');
                    } else {
                        $parts = parse_url($uri);
                        if (!isset($parts['scheme']) && !in_array($uri[0], [ '/', '#', '?' ])) {
                            $path  = ($parts['path'] ?? '');
                            $query = ($parts['query'] ?? '');
                            $hash  = ($parts['fragment'] ?? '');
                            $uri = $baseUrl->withPath($path)
                                           ->withQuery($query)
                                           ->withFragment($hash);
                        }
                    }

                    return (string)$uri;
                },
                'renderContext' => fn($text, ?LambdaHelper $helper = null) => $helper->render('{{>' . $helper->render($text) . '}}'),
            ];

            return array_merge($helpers, $urls);
        });
    }

    /**
     * @param Container $container The DI container.
     */
    protected function registerTwigHelpersServices(Container $container): void
    {
        if (!isset($container['view/twig/helpers'])) {
            $container['view/twig/helpers'] = (fn(): array => []);
        }

        /**
         * Url helpers for Twig.
         *
         * @return TwigUrlHelpers
         */
        $container['view/twig/helpers/url'] = (fn(Container $container): TwigHelpersInterface => new TwigUrlHelpers([
            'baseUrl' => $container['base-url'],
        ]));

        /**
         * Debug helpers for Twig.
         *
         * @return TwigDebugHelpers
         */
        $container['view/twig/helpers/debug'] = (fn(Container $container): TwigHelpersInterface => new TwigDebugHelpers([
            'debug'  => $container['debug'],
        ]));

        /**
         * Extend global helpers for the Twig Engine.
         *
         * @param  array     $helpers   The Mustache helper collection.
         * @param  Container $container A container instance.
         * @return array
         */
        $container->extend('view/twig/helpers', fn(array $helpers, Container $container): array => array_merge(
            $helpers,
            $container['view/twig/helpers/url']->toArray(),
            $container['view/twig/helpers/debug']->toArray(),
        ));
    }
}
