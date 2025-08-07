<?php

namespace Charcoal\App\ServiceProvider;

// From PSR-7
use Charcoal\Factory\GenericResolver;
use DI\Container;
// From Slim
use Nyholm\Psr7\Uri;
// From Mustache
use Mustache_LambdaHelper as LambdaHelper;
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
use Charcoal\App\ServiceProvider\DatabaseServiceProvider;
use Charcoal\App\ServiceProvider\FilesystemServiceProvider;
use Charcoal\App\ServiceProvider\ScriptServiceProvider;
use Charcoal\App\ServiceProvider\LoggerServiceProvider;
use Charcoal\App\Template\TemplateInterface;
use Charcoal\App\Template\WidgetInterface;
use Charcoal\App\Template\WidgetBuilder;
use Charcoal\View\ViewServiceProvider;
use Psr\Container\ContainerInterface;
use Charcoal\App\Handler\HandlerInterface;

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
class AppServiceProvider
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A service container.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerKernelServices($container);

        (new CacheServiceProvider())->register($container);
        (new DatabaseServiceProvider())->register($container);
        (new FilesystemServiceProvider())->register($container);
        (new LoggerServiceProvider())->register($container);
        (new ScriptServiceProvider())->register($container);
        (new TranslatorServiceProvider())->register($container);
        (new ViewServiceProvider())->register($container);

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
        if (!$container->has('config')) {
            $container->set('config', new AppConfig());
        }

        if (!$container->has('debug')) {
            /**
             * Application Debug Mode
             *
             * @param  Container $container A service container.
             * @return boolean
             */
            $container->set('debug', function (Container $container) {
                if (!empty($container->get('config')['debug'])) {
                    return !!$container->get('config')['debug'];
                }

                if (!empty($container->get('config')['dev_mode'])) {
                    return !!$container->get('config')['dev_mode'];
                }

                return false;
            });
        }

        if (!($container->has('base-url'))) {
            /**
             * Base URL as a PSR-7 UriInterface object for the current request
             * or the Charcoal application.
             *
             * @param  Container $container A service container.
             * @return \Psr\Http\Message\UriInterface
             */
            $container->set('base-url', function (ContainerInterface $container) {
                if (!empty($container->get('config')['base_url'])) {
                    $baseUrl = $container->get('config')['base_url'];
                } else {
                    $uri = $container->get('request')->getUri();
                    $baseUrl = $uri->getScheme() . '://' . $uri->getHost();
                }

                $baseUrl = (new Uri($baseUrl))->withUserInfo('');

                /** Fix the base path */
                $path = $baseUrl->getPath();
                if ($path) {
                    $baseUrl = $baseUrl->withPath($path . '/');
                }

                return $baseUrl;
            });
        }
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerHandlerServices(Container $container)
    {
        $container->set('phpErrorHandler/class', PhpError::class);
        $container->set('errorHandler/class', Error::class);
        $container->set('notFoundHandler/class', NotFound::class);
        $container->set('notAllowedHandler/class', NotAllowed::class);
        $container->set('maintenanceHandler/class', Maintenance::class);

        $handlersConfig = $container->get('config')['handlers'];

        /**
         * HTTP 404 (Not Found) handler.
         */
        $container->set('notFoundHandler', function (Container $container) use ($handlersConfig) {
            $config  = ($handlersConfig['notFound'] ?? []);
            $class   = $container->get('notFoundHandler/class');
            /** @var HandlerInterface $handler */
            $handler = new $class($container, $config);
            $handler->init();
            return $handler;
        });

        /**
         * HTTP 405 (Not Allowed) handler.
         */
        $container->set('notAllowedHandler', function (Container $container) use ($handlersConfig) {
            $config  = ($handlersConfig['notAllowed'] ?? []);
            $class   = $container->get('notAllowedHandler/class');
            /** @var HandlerInterface $handler */
            $handler = new $class($container, $config);
            $handler->init();
            return $handler;
        });

        /**
         * HTTP 500 (Error) handler.
         */
        if (!$container->has('errorHandler')) {
            $container->set('errorHandler', function (Container $container) use ($handlersConfig) {
                $config  = ($handlersConfig['error'] ?? []);
                $class   = $container->get('errorHandler/class');
                $handler = new $class($container, $config);
                /** @var HandlerInterface $handler */
                $handler->init();
                return $handler;
            });
        }

        /**
         * HTTP 503 (Service Unavailable) handler.
         * This handler is not part of Slim.
         */
        $container->set('maintenanceHandler', function (Container $container) use ($handlersConfig) {
            $config  = ($handlersConfig['maintenance'] ?? []);
            $class   = $container->get('maintenanceHandler/class');
            $handler = new $class($container, $config);
            return $handler->init();
        });
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function registerRouteServices(Container $container)
    {
        $container->set('route/controller/action/class', ActionRoute::class);
        $container->set('route/controller/template/class', TemplateRoute::class);

        /**
         * The Route Factory service is used to instanciate new routes.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container->set('route/factory', function (Container $container) {
            return new Factory([
                'base_class'       => RouteInterface::class,
                'resolver_options' => [
                    'suffix' => 'Route',
                ],
                'arguments'  => [
                    [
                        'logger' => $container->get('logger'),
                    ],
                ],
            ]);
        });
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
        $container->set('middlewares/charcoal/app/middleware/ip', function (container $container) {
            $wareConfig = $container->get('config')['middlewares']['charcoal/app/middleware/ip'];
            return new IpMiddleware($wareConfig);
        });
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
        $container->set('action/factory', function (Container $container) {
            return new Factory([
                'base_class'       => ActionInterface::class,
                'resolver_options' => [
                    'suffix' => 'Action',
                ],
                'arguments' => [
                    [
                        'container' => $container,
                        'logger'    => $container->get('logger'),
                    ],
                ],
            ]);
        });

        /**
         * The Template Factory service is used to instanciate new templates.
         *
         * - Templates are `TemplateInterface` and must be suffixed with `Template`.
         * - The container is passed to the created template constructor, which will call `setDependencies()`.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container->set('template/factory', function (Container $container) {
            return new Factory([
                'base_class'       => TemplateInterface::class,
                'resolver_options' => [
                    'suffix' => 'Template',
                ],
                'arguments' => [
                    [
                        'container' => $container,
                        'logger'    => $container->get('logger'),
                    ],
                ],
            ]);
        });

        /**
         * The Widget Factory service is used to instanciate new widgets.
         *
         * - Widgets are `WidgetInterface` and must be suffixed with `Widget`.
         * - The container is passed to the created widget constructor, which will call `setDependencies()`.
         *
         * @param  Container $container A service container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container->set('widget/factory', function (Container $container) {
            return new Factory([
                'base_class'       => WidgetInterface::class,
                'resolver_options' => [
                    'suffix' => 'Widget',
                ],
                'arguments' => [
                    [
                        'container' => $container,
                        'logger'    => $container->get('logger'),
                    ],
                ],
            ]);
        });

        /**
         * @param  Container $container A service container.
         * @return WidgetBuilder
         */
        $container->set('widget/builder', function (Container $container) {
            return new WidgetBuilder($container->get('widget/factory'), $container);
        });
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
        $container->set('module/factory', function (Container $container) {
            return new Factory([
                'base_class'       => ModuleInterface::class,
                'resolver_options' => [
                    'suffix' => 'Module',
                ],
                'arguments'  => [
                    [
                        'logger' => $container->get('logger'),
                    ],
                ],
            ]);
        });

        /**
         * The modules as PHP classes.
         *
         * @param  Container $container A service container.
         * @return array
         */
        $container->set('module/classes', function (Container $container) {
            $appConfig = $container->get('config');

            $modules = $appConfig['modules'];
            $modules = array_keys($modules);

            $moduleResolver = new GenericResolver([
                'suffix' => 'Module',
            ]);

            $modules = array_map(function ($module) use ($moduleResolver) {
                return $moduleResolver->resolve($module);
            }, $modules);

            array_filter($modules, function ($class) {
                return class_exists($class);
            });

            return $modules;
        });
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
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerMustacheHelpersServices(Container $container): void
    {
        /**
         * Extend helpers for the Mustache Engine
         *
         * @return array
         */
        $helpers = $container->has('view/mustache/helpers') ? $container->get('view/mustache/helpers') : [];
        $container->set('view/mustache/helpers', function (Container $container) use ($helpers): array {
            $baseUrl = $container->get('base-url');
            $urls = [
                /**
                 * Application debug mode.
                 *
                 * @return boolean
                 */
                'debug' => ($container->get('config')['debug'] ?? false),
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
                    if ($helper) {
                        $uri = $helper->render($uri);
                    }

                    $uri = strval($uri);
                    if ($uri === '') {
                        $uri = $baseUrl->withPath('');
                    } else {
                        $parts = parse_url($uri);
                        if (!isset($parts['scheme'])) {
                            if (!in_array($uri[0], [ '/', '#', '?' ])) {
                                $path  = isset($parts['path']) ? $parts['path'] : '';
                                $query = isset($parts['query']) ? $parts['query'] : '';
                                $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';

                                $uri = $baseUrl->withPath($path)
                                               ->withQuery($query)
                                               ->withFragment($hash);
                            }
                        }
                    }

                    return (string)$uri;
                },
                'renderContext' => function ($text, ?LambdaHelper $helper = null) {
                    return $helper->render('{{>' . $helper->render($text) . '}}');
                },
            ];

            return array_merge($helpers, $urls);
        });
    }
}
