<?php

namespace Charcoal\Admin;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-app'
use Charcoal\App\Handler\HandlerInterface;
use Charcoal\App\Module\AbstractModule;
// From 'charcoal-admin'
use Charcoal\Admin\ServiceProvider\AdminServiceProvider;
use Charcoal\App\Handler\HandlerConfig;
use DI\Container;
use Slim\Interfaces\RouteCollectorProxyInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\MiddlewareDispatcher;

/**
 * Charcoal Administration Module
 */
class AdminModule extends AbstractModule
{
    /**
     * Charcoal Administration Setup.
     *
     * This module is bound to the `/admin` URL.
     *
     * ## Provides
     *
     * - `charcoal/admin/module` An instance of this module
     *   - Exact type: `\Charcoal\Admin\AdminModule`
     *   - which implements `\Charcoal\Module\ModuleInterface`
     *
     * ## Dependencies
     * - `charcoal/config` Provided by \Charcoal\CharcoalModule
     *
     * @return AdminModule Chainable
     */
    public function setUp()
    {
        // Hack: skip if the request does not start with '/admin'
        /** @var Container $container */
        $container = $this->app()->getContainer();
        if ($this->isPathAdmin($container->get('request')->getUri()->getPath()) !== true) {
            return $this;
        }

        // A session is necessary for the admin module
        if (session_id() === '') {
            session_start();
        }
        (new AdminServiceProvider())->register($container);

        $module = $this;
        $container->set('charcoal/admin/module', function () use ($module) {
            return $module;
        });

        $adminConfig = $container->get('admin/config');

        $this->setConfig($adminConfig);

        $groupIdent = '/' . trim($adminConfig['base_path'], '/');

        // Add the route group
        $this->app()->group($groupIdent, [$this, 'setupRoutes'])
            ->add([$this, 'setupHandlers']);

        /*foreach ($this->app()->getRouteCollector()->getRoutes() as $route) {
            echo implode(', ', $route->getMethods()) . ' - ' . $route->getPattern();
            echo "<br>";
        }
        exit;*/

        return $this;
    }

    /**
     * Set up the module's routes and handlers.
     *
     * @return AdminModule Chainable
     */
    public function setupRoutes(?RouteCollectorProxyInterface $group = null)
    {
        if ($this->routeManager === null) {
            parent::setupRoutes($group);

            // Serve the Admin's "Not Found" handler for the Admin's route group.
            $group->any('{catchall:.*}', 'notFoundHandler');
        }

        return $this;
    }

    /**
     * Set up the module's handlers, via group middleware.
     *
     * @param  RequestInterface  $request  A PSR7 request object.
     * @param  ResponseInterface $response A PSR7 response object.
     * @param  callable          $next     The next callable middleware.
     * @return ResponseInterface A PSR7 response object.
     */
    public function setupHandlers(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /** @var Container */
        $container = $this->app()->getContainer();

        /**
         * HTTP 404 (Not Found) handler.
         *
         * @param  object|HandlerInterface $handler An error handler instance.
         * @return HandlerInterface
         */
        /*$container->extend('notFoundHandler', function ($handler, $container) {
            $appConfig = $container->get('config');
            $adminConfig = $container->get('admin/config');
            if ($handler instanceof HandlerInterface) {
                $config = $handler->createConfig($appConfig['handlers.defaults']);
                $config->merge($adminConfig['handlers.defaults']);

                if (!empty($adminConfig['handlers.notFound'])) {
                    $config->merge($adminConfig['handlers.notFound']);
                }

                $handler->setConfig($config)->init();
            }

            return $handler;
        });*/

        /**
         * HTTP 405 (Not Allowed) handler.
         *
         * @param  object|HandlerInterface $handler An error handler instance.
         * @return HandlerInterface
         */
        /*$container->extend('notAllowedHandler', function ($handler, $container) {
            $appConfig = $container->get('config');
            $adminConfig = $container->get('admin/config');
            if ($handler instanceof HandlerInterface) {
                $config = $handler->createConfig($appConfig['handlers.defaults']);
                $config->merge($adminConfig['handlers.defaults']);

                if (!empty($adminConfig['handlers.notAllowed'])) {
                    $config->merge($adminConfig['handlers.notAllowed']);
                }

                $handler->setConfig($config)->init();
            }

            return $handler;
        });*/

        /**
         * HTTP 500 (Error) handler for PHP 7+ Throwables.
         *
         * @param  object|HandlerInterface $handler An error handler instance.
         * @return HandlerInterface
         */
        /*$container->extend('phpErrorHandler', function ($handler, $container) {
            $appConfig = $container->get('config');
            $adminConfig = $container->get('admin/config');
            if ($handler instanceof HandlerInterface) {
                $config = $handler->createConfig($appConfig['handlers.defaults']);
                $config->merge($adminConfig['handlers.defaults']);

                if (!empty($adminConfig['handlers.phpError'])) {
                    $config->merge($adminConfig['handlers.phpError']);
                }

                $handler->setConfig($config)->init();
            }

            return $handler;
        });*/

        /**
         * HTTP 500 (Error) handler.
         *
         * @param  object|HandlerInterface $handler An error handler instance.
         * @return HandlerInterface
         */
        $container->set('errorHandler', function (Container $container) {
            $appConfig = $container->get('config');
            $adminConfig = $container->get('admin/config');
            $config = new HandlerConfig($appConfig['handlers.defaults']);
            $config->merge($adminConfig['handlers.defaults']);

            if (!empty($adminConfig['handlers.error'])) {
                $config->merge($adminConfig['handlers.error']);
            }

            $handlerClass = $container->get('errorHandler/class');
            $handler = new $handlerClass($container, $config);
            $handler->init();

            return $handler;
        });
        /*$container->set('errorHandler', function ($handler, $container) {
            $appConfig = $container->get('config');
            $adminConfig = $container->get('admin/config');
            if ($handler instanceof HandlerInterface) {
                $config = new HandlerConfig($appConfig['handlers.defaults']);
                $config->merge($adminConfig['handlers.defaults']);

                if (!empty($adminConfig['handlers.error'])) {
                    $config->merge($adminConfig['handlers.error']);
                }

                $handler->setConfig($config)->init();
            }

            return $handler;
        });*/

        /**
         * HTTP 503 (Service Unavailable) handler.
         *
         * This handler is not part of Slim.
         *
         * @param  object|HandlerInterface $handler An error handler instance.
         * @return HandlerInterface
         */
        /*$container->extend('maintenanceHandler', function ($handler, $container) {
            $appConfig = $container->get('config');
            $adminConfig = $container->get('admin/config');
            if ($handler instanceof HandlerInterface) {
                $config = new HandlerConfig($appConfig['handlers.defaults']);
                $config->merge($adminConfig['handlers.defaults']);

                if (!empty($adminConfig['handlers.maintenance'])) {
                    $config->merge($adminConfig['handlers.maintenance']);
                }

                $handler->setConfig($config)->init();
            }

            return $handler;
        });*/

        return $handler->handle($request);
    }

    /**
     * @param string $path The path to check.
     * @return boolean
     */
    private function isPathAdmin($path)
    {
        $path = ltrim($path, '/');
        if ($path === 'admin') {
            return true;
        }

        if (substr($path, 0, 6) === 'admin/') {
            return true;
        }

        return false;
    }
}
